<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreShipmentRequest;
use App\Http\Requests\UpdateShipmentRequest;
use App\Models\Branch;
use App\Models\Shipment;
use App\Models\TrackingEvent;
use App\Services\AuditService;
use App\Services\ResiGenerator;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ShipmentController extends Controller
{
    public function index(Request $request)
    {
        $allowedSorts = ['resi_no','origin_city','destination_city','total_koli','shipment_status','created_at'];
        $sortCol = in_array($request->sort, $allowedSorts) ? $request->sort : 'created_at';
        $sortDir = $request->has('sort') ? ($request->dir === 'desc' ? 'desc' : 'asc') : 'desc';

        $shipments = Shipment::with(['originBranch', 'destinationBranch'])
            ->when($request->status, fn($q, $s) => $q->where('shipment_status', $s))
            ->when($request->search, fn($q, $s) => $q->where(function($q2) use ($s) {
                $q2->where('resi_no', 'like', "%$s%")
                   ->orWhere('sender_name', 'like', "%$s%")
                   ->orWhere('receiver_name', 'like', "%$s%");
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
            $shipments->whereBetween('created_at', [$start->toDateTimeString(), $end->toDateTimeString()]);
        }

        // Sort by city name via subquery (not UUID)
        if ($sortCol === 'origin_city') {
            $shipments->orderByRaw('(SELECT city FROM branches WHERE branches.id = shipments.origin_branch_id) ' . $sortDir);
        } elseif ($sortCol === 'destination_city') {
            $shipments->orderByRaw('(SELECT city FROM branches WHERE branches.id = shipments.destination_branch_id) ' . $sortDir);
        } else {
            $shipments->orderBy($sortCol, $sortDir);
        }

        $shipments = $shipments->paginate(10);
        $branches = Branch::all();
        return view('shipments.index', compact('shipments', 'branches'));
    }

    public function create()
    {
        $branches = Branch::all();
        return view('shipments.create', compact('branches'));
    }

    public function store(StoreShipmentRequest $request)
    {
        $data = $request->validated();
        $data['resi_no'] = ResiGenerator::generate();
        $data['shipment_status'] = 'Created';
        $data['origin_branch_id'] = auth()->user()->branch_id;

        // NOTE: customer_id is required (See StoreShipmentRequest). No auto-create fallback.

        $shipment = Shipment::create($data);

        // Auto-create or link Purchase Order if po_number provided
        if (!empty($data['po_number']) && !empty($data['customer_id'])) {
            $po = \App\Models\PurchaseOrder::firstOrCreate(
                ['po_number' => $data['po_number']],
                [
                    'customer_id' => $data['customer_id'],
                    'total_amount' => 0,
                    'paid_amount' => 0,
                    'status' => 'draft',
                ]
            );
            // PO total will be set when invoice is created (no placeholder calc here)
            // Keep total_amount = 0 until invoice calculation is done
        }

        // Create tracking event
        TrackingEvent::create([
            'shipment_id' => $shipment->id,
            'event_type' => 'Created',
            'actor_id' => auth()->id(),
            'event_time' => now(),
        ]);

        AuditService::log('Create Shipment', 'CREATE', $shipment);
        return redirect()->route('shipments.show', $shipment)->with('success', 'Resi berhasil dibuat');
    }

    public function show($id)
    {
        $shipment = Shipment::withoutGlobalScopes()->findOrFail($id);
        $shipment->load(['originBranch', 'destinationBranch', 'package', 'manifests.originBranch', 'manifests.destinationBranch', 'trackingEvents' => fn($q) => $q->latest()]);
        return view('shipments.show', compact('shipment'));
    }

    public function edit(Shipment $shipment)
    {
        $shipment = Shipment::withoutGlobalScopes()->findOrFail($shipment->id);
        $branches = Branch::all();
        return view('shipments.edit', compact('shipment', 'branches'));
    }

    public function update(UpdateShipmentRequest $request, Shipment $shipment)
    {
        $shipment = Shipment::withoutGlobalScopes()->findOrFail($shipment->id);
        $shipment->update($request->validated());
        AuditService::log('Update Shipment', 'UPDATE', $shipment);
        return redirect()->route('shipments.show', $shipment)->with('success', 'Resi diperbarui');
    }

    public function destroy(Shipment $shipment)
    {
        $shipment = Shipment::withoutGlobalScopes()->findOrFail($shipment->id);
        AuditService::log('Delete Shipment', 'DELETE', $shipment);
        $shipment->delete();
        return redirect()->route('shipments.index')->with('success', 'Resi dihapus');
    }

    public function hold($id)
    {
        $shipment = Shipment::withoutGlobalScopes()->findOrFail($id);
        if (in_array($shipment->shipment_status, ['Delivered', 'Cancelled'])) {
            return back()->withErrors(['status' => 'Resi sudah ' . $shipment->shipment_status . ', tidak bisa di-hold.']);
        }
        // Remove from any manifest
        \App\Models\ManifestItem::where('shipment_id', $shipment->id)->delete();
        $shipment->update(['shipment_status' => 'On Hold']);
        TrackingEvent::create([
            'shipment_id' => $shipment->id,
            'event_type' => 'On Hold',
            'location' => $shipment->originBranch?->city ?? '-',
            'event_time' => now(),
            'actor_id' => auth()->id(),
            'notes' => 'Resi ditahan (hold), dikeluarkan dari manifest',
        ]);
        AuditService::log('Hold Shipment', 'HOLD', $shipment);
        return back()->with('success', 'Resi ' . $shipment->resi_no . ' ditahan dan dikeluarkan dari manifest.');
    }

    public function cancel($id)
    {
        $shipment = Shipment::withoutGlobalScopes()->findOrFail($id);
        if ($shipment->shipment_status === 'Delivered') {
            return back()->withErrors(['status' => 'Resi sudah Delivered, tidak bisa dibatalkan.']);
        }
        // Remove from any manifest
        \App\Models\ManifestItem::where('shipment_id', $shipment->id)->delete();
        $shipment->update(['shipment_status' => 'Cancelled']);
        TrackingEvent::create([
            'shipment_id' => $shipment->id,
            'event_type' => 'Cancelled',
            'location' => $shipment->originBranch?->city ?? '-',
            'event_time' => now(),
            'actor_id' => auth()->id(),
            'notes' => 'Resi dibatalkan, dikeluarkan dari manifest',
        ]);
        AuditService::log('Cancel Shipment', 'CANCEL', $shipment);
        return back()->with('success', 'Resi ' . $shipment->resi_no . ' dibatalkan dan dikeluarkan dari manifest.');
    }

    public function exportCsv(Request $request)
    {
        $shipments = $this->filteredShipments($request);
        $filename = 'shipments_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        $callback = function () use ($shipments) {
            $file = fopen('php://output', 'w');
            // BOM for Excel UTF-8
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, ['Resi', 'Asal', 'Tujuan', 'Koli', 'Status', 'Dibuat', 'Pengirim', 'Penerima']);
            foreach ($shipments as $s) {
                fputcsv($file, [
                    $s->resi_no,
                    $s->originBranch?->city ?? '-',
                    $s->destinationBranch?->city ?? '-',
                    $s->total_koli,
                    $s->shipment_status,
                    $s->created_at?->format('d/m/Y H:i'),
                    $s->sender_name,
                    $s->receiver_name,
                ]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf(Request $request)
    {
        $shipments = $this->filteredShipments($request);
        $generated_at = now();
        $pdf = Pdf::loadView('print.shipment-list', compact('shipments', 'generated_at'));
        if ($request->get('preview')) {
            return $pdf->stream('shipments_' . date('Y-m-d_His') . '.pdf');
        }
        return $pdf->download('shipments_' . date('Y-m-d_His') . '.pdf');
    }

    private function filteredShipments(Request $request)
    {
        $shipments = Shipment::with(['originBranch', 'destinationBranch'])
            ->when($request->status, fn($q, $s) => $q->where('shipment_status', $s))
            ->when($request->search, fn($q, $s) => $q->where(function($q2) use ($s) {
                $q2->where('resi_no', 'like', "%$s%")
                   ->orWhere('sender_name', 'like', "%$s%")
                   ->orWhere('receiver_name', 'like', "%$s%");
            }))
            ->when($request->branch_id, fn($q, $b) => $q->where(function($q2) use ($b) {
                $q2->where('origin_branch_id', $b)
                   ->orWhere('destination_branch_id', $b);
            }));

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $start = \Carbon\Carbon::parse($request->start_date)->startOfDay();
            $end = \Carbon\Carbon::parse($request->end_date)->endOfDay();
            $shipments->whereBetween('created_at', [$start->toDateTimeString(), $end->toDateTimeString()]);
        }

        return $shipments->orderBy('created_at', 'desc')->get();
    }

}
