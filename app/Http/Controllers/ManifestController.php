<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreManifestRequest;
use App\Http\Requests\DispatchManifestRequest;
use App\Http\Requests\ReceiveManifestRequest;
use App\Models\Manifest;
use App\Models\Shipment;
use App\Models\TrackingEvent;
use App\Services\AuditService;
use App\Services\ManifestGenerator;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ManifestController extends Controller
{
    public function index(Request $request)
    {
        $allowedSorts = ['manifest_no','origin_city','destination_city','manifest_status','vehicle_no','driver_name','created_at'];
        $sortCol = in_array($request->sort, $allowedSorts) ? $request->sort : 'created_at';
        $sortDir = $request->has('sort') ? ($request->dir === 'desc' ? 'desc' : 'asc') : 'desc';

        $manifests = Manifest::with(['originBranch', 'destinationBranch'])
            ->when($request->status, fn($q, $s) => $q->where('manifest_status', $s))
            ->when($request->search, fn($q, $s) => $q->where(function($q2) use ($s) {
                $q2->where('manifest_no', 'like', "%$s%")
                   ->orWhere('vehicle_no', 'like', "%$s%")
                   ->orWhere('driver_name', 'like', "%$s%");
            }))
            ->when($request->branch_id, fn($q, $b) => $q->where(function($q2) use ($b) {
                $q2->where('origin_branch_id', $b)
                   ->orWhere('destination_branch_id', $b);
            }));

        // Date range filter — max 90 days
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $start = Carbon::parse($request->start_date)->startOfDay();
            $end = Carbon::parse($request->end_date)->endOfDay();
            if ($start->diffInDays($end) > 90) {
                $start = Carbon::today()->subDays(90)->startOfDay();
                $end = Carbon::today()->endOfDay();
                session()->flash('warning', 'Rentang tanggal maksimal 90 hari. Filter disesuaikan.');
            }
            $manifests->whereBetween('created_at', [$start->toDateTimeString(), $end->toDateTimeString()]);
        }

        if ($sortCol === 'origin_city') {
            $manifests->orderByRaw('(SELECT city FROM branches WHERE branches.id = manifests.origin_branch_id) ' . $sortDir);
        } elseif ($sortCol === 'destination_city') {
            $manifests->orderByRaw('(SELECT city FROM branches WHERE branches.id = manifests.destination_branch_id) ' . $sortDir);
        } else {
            $manifests->orderBy($sortCol, $sortDir);
        }

        $manifests = $manifests->paginate(10);
        $branches = \App\Models\Branch::all();
        return view('manifests.index', compact('manifests', 'branches'));
    }


    public function exportCsv(Request $request)
    {
        $manifests = $this->filteredManifests($request);
        $filename = 'manifests_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        $callback = function () use ($manifests) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, ['No. Manifest', 'Asal', 'Tujuan', 'Status', 'No. Kendaraan', 'Driver', 'Dibuat']);
            foreach ($manifests as $m) {
                fputcsv($file, [
                    $m->manifest_no,
                    $m->originBranch?->city ?? '-',
                    $m->destinationBranch?->city ?? '-',
                    $m->manifest_status,
                    $m->vehicle_no,
                    $m->driver_name,
                    $m->created_at?->format('d/m/Y H:i'),
                ]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf(Request $request)
    {
        $manifests = $this->filteredManifests($request);
        $generated_at = now();
        $pdf = Pdf::loadView('print.manifest-list', compact('manifests', 'generated_at'));
        if ($request->get('preview')) {
            return $pdf->stream('manifests_' . date('Y-m-d_His') . '.pdf');
        }
        return $pdf->download('manifests_' . date('Y-m-d_His') . '.pdf');
    }

    private function filteredManifests(Request $request)
    {
        $manifests = Manifest::with(['originBranch', 'destinationBranch'])
            ->when($request->status, fn($q, $s) => $q->where('manifest_status', $s))
            ->when($request->search, fn($q, $s) => $q->where(function($q2) use ($s) {
                $q2->where('manifest_no', 'like', "%$s%")
                   ->orWhere('vehicle_no', 'like', "%$s%")
                   ->orWhere('driver_name', 'like', "%$s%");
            }))
            ->when($request->branch_id, fn($q, $b) => $q->where(function($q2) use ($b) {
                $q2->where('origin_branch_id', $b)
                   ->orWhere('destination_branch_id', $b);
            }));

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $start = \Carbon\Carbon::parse($request->start_date)->startOfDay();
            $end = \Carbon\Carbon::parse($request->end_date)->endOfDay();
            $manifests->whereBetween('created_at', [$start->toDateTimeString(), $end->toDateTimeString()]);
        }

        return $manifests->orderBy('created_at', 'desc')->get();
    }

    public function create()
    {
        $originBranch = auth()->user()->branch_id;
        $branches = \App\Models\Branch::all();
        $availableShipments = Shipment::withoutGlobalScopes()
            ->where('origin_branch_id', $originBranch)
            ->whereDoesntHave('manifests')
            ->get()
            ->groupBy('destination_branch_id');
        return view('manifests.create', compact('branches', 'availableShipments'));
    }

    public function store(StoreManifestRequest $request)
    {
        $data = $request->validated();
        $data['manifest_no'] = ManifestGenerator::generate();
        $data['manifest_status'] = 'Draft';
        $data['origin_branch_id'] = auth()->user()->branch_id;
        $manifest = Manifest::create($data);

        if (isset($data['shipment_ids'])) {
            // Validate: all shipments must have the same destination_branch_id as the manifest
            $shipments = Shipment::withoutGlobalScopes()->whereIn('id', $data['shipment_ids'])->get();
            foreach ($shipments as $s) {
                if ($s->destination_branch_id !== $data['destination_branch_id']) {
                    $manifest->shipments()->detach();
                    $manifest->delete();
                    return back()->withErrors([
                        'shipment_ids' => 'Semua shipment harus memiliki tujuan ke ' .
                            $manifest->destinationBranch?->name . ' — resi ' . $s->resi_no . ' tujuannya berbeda.',
                    ]);
                }
            }
            foreach ($data['shipment_ids'] as $shipmentId) {
                \App\Models\ManifestItem::create([
                    'manifest_id' => $manifest->id,
                    'shipment_id' => $shipmentId,
                ]);
            }
        }

        AuditService::log('Create Manifest', 'CREATE', $manifest);
        return redirect()->route('manifests.show', $manifest)->with('success', 'Manifest dibuat');
    }

    public function show($id)
    {
        $manifest = Manifest::withoutGlobalScopes()->findOrFail($id);
        $manifest->load(['originBranch', 'destinationBranch', 'items.shipment']);
        return view('manifests.show', compact('manifest'));
    }

    public function edit(Manifest $manifest)
    {
        $manifest = Manifest::withoutGlobalScopes()->findOrFail($manifest->id);
        $branches = \App\Models\Branch::all();
        return view('manifests.edit', compact('manifest', 'branches'));
    }

    public function update(Request $request, Manifest $manifest)
    {
        $manifest = Manifest::withoutGlobalScopes()->findOrFail($manifest->id);
        $manifest->update($request->only(['vehicle_no', 'driver_name', 'origin_branch_id', 'destination_branch_id']));
        AuditService::log('Update Manifest', 'UPDATE', $manifest);
        return redirect()->route('manifests.show', $manifest)->with('success', 'Manifest diperbarui');
    }

    public function destroy(Manifest $manifest)
    {
        $manifest = Manifest::withoutGlobalScopes()->findOrFail($manifest->id);
        AuditService::log('Delete Manifest', 'DELETE', $manifest);
        $manifest->delete();
        return redirect()->route('manifests.index')->with('success', 'Manifest dihapus');
    }

    public function dispatch(DispatchManifestRequest $request, $id)
    {
        $manifest = Manifest::withoutGlobalScopes()->findOrFail($id);
        if ($manifest->origin_branch_id !== auth()->user()->branch_id && !auth()->user()->isSuperAdmin()) {
            return back()->withErrors(['access' => 'Hanya cabang asal yang dapat dispatch']);
        }
        $manifest->update([
            'manifest_status' => 'Dispatched',
            'dispatch_time' => now(),

        ]);
        // Sync shipment status: Created → In Transit
        $manifest->shipments()->update(['shipment_status' => 'In Transit']);
        // Create tracking events
        foreach ($manifest->shipments as $s) {
            TrackingEvent::create([
                'shipment_id' => $s->id,
                'event_type' => 'In Transit',
                'location' => $manifest->originBranch?->name,
                'actor_id' => auth()->id(),
                'event_time' => now(),
                'notes' => 'Manifest ' . $manifest->manifest_no . ' dispatched',
            ]);
        }
        AuditService::log('Dispatch Manifest', 'DISPATCH', $manifest, [
            'manifest_no' => $manifest->manifest_no,
            'shipments' => $manifest->shipments->pluck('resi_no')->toArray(),

        ]);
        return back()->with('success', 'Manifest dispatched');
    }

    public function receive(ReceiveManifestRequest $request, $id)
    {
        $manifest = Manifest::withoutGlobalScopes()->findOrFail($id);
        if ($manifest->destination_branch_id !== auth()->user()->branch_id && !auth()->user()->isSuperAdmin()) {
            return back()->withErrors(['access' => 'Hanya cabang tujuan yang dapat menerima']);
        }
        $manifest->update([
            'manifest_status' => 'Received',
            'receive_time' => now(),
        ]);
        // Sync shipment status: In Transit → Arrived at Destination
        $manifest->shipments()->update(['shipment_status' => 'Arrived at Destination']);
        // Create tracking events
        foreach ($manifest->shipments as $s) {
            TrackingEvent::create([
                'shipment_id' => $s->id,
                'event_type' => 'Arrived',
                'location' => $manifest->destinationBranch?->name,
                'actor_id' => auth()->id(),
                'event_time' => now(),
                'notes' => 'Manifest ' . $manifest->manifest_no . ' received at destination branch',
            ]);
        }
        AuditService::log('Receive Manifest', 'RECEIVE', $manifest, [
            'manifest_no' => $manifest->manifest_no,
            'shipments' => $manifest->shipments->pluck('resi_no')->toArray(),
            'receive_time' => now()->toDateTimeString(),
        ]);
        return back()->with('success', 'Manifest diterima');
    }

    public function complete(Request $request, $id)
    {
        $manifest = Manifest::withoutGlobalScopes()->findOrFail($id);
        if ($manifest->destination_branch_id !== auth()->user()->branch_id && !auth()->user()->isSuperAdmin()) {
            return back()->withErrors(['access' => 'Hanya cabang tujuan yang dapat menyelesaikan']);
        }
        if ($manifest->manifest_status !== 'Received') {
            return back()->withErrors(['status' => 'Manifest harus berstatus Received terlebih dahulu']);
        }
        $manifest->update([
            'manifest_status' => 'Completed',
        ]);
        // Sync shipment status: Arrived at Destination → Delivered
        $manifest->shipments()->update(['shipment_status' => 'Delivered']);
        // Create tracking events
        foreach ($manifest->shipments as $s) {
            TrackingEvent::create([
                'shipment_id' => $s->id,
                'event_type' => 'Delivered',
                'location' => $manifest->destinationBranch?->name,
                'actor_id' => auth()->id(),
                'event_time' => now(),
                'notes' => 'Manifest ' . $manifest->manifest_no . ' completed — delivered to customer',
            ]);
        }
        AuditService::log('Complete Manifest', 'COMPLETE', $manifest, [
            'manifest_no' => $manifest->manifest_no,
            'shipments' => $manifest->shipments->pluck('resi_no')->toArray(),
        ]);
        return back()->with('success', 'Manifest selesai');
    }

}
