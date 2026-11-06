<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Manifest;
use App\Models\Shipment;
use App\Services\ManifestGenerator;
use Illuminate\Http\Request;

class ManifestController extends Controller
{
    public function index(Request $request)
    {
        $manifests = Manifest::with(['originBranch', 'destinationBranch'])
            ->when($request->status, fn($q, $s) => $q->where('manifest_status', $s))
            ->paginate(15);
        return response()->json($manifests);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'destination_branch_id' => 'required|exists:branches,id',
            'vehicle_no' => 'nullable|string',
            'shipment_ids' => 'array',
        ]);
        $data['origin_branch_id'] = auth()->user()->branch_id;
        $data['manifest_no'] = ManifestGenerator::generate();
        $data['manifest_status'] = 'Draft';
        $manifest = Manifest::create($data);
        if (isset($data['shipment_ids'])) $manifest->shipments()->attach($data['shipment_ids']);
        return response()->json($manifest, 201);
    }

    public function show($id)
    {
        $manifest = Manifest::withoutGlobalScopes()->findOrFail($id);
        $manifest->load(['items', 'originBranch', 'destinationBranch']);
        return response()->json($manifest);
    }

    public function dispatch(Request $request, $id)
    {
        $manifest = Manifest::withoutGlobalScopes()->findOrFail($id);
        $manifest->update([
            'manifest_status' => 'Dispatched',
            'dispatch_time' => now(),
        ]);
        return response()->json(['message' => 'Dispatched']);
    }

    public function receive(Request $request, $id)
    {
        $manifest = Manifest::withoutGlobalScopes()->findOrFail($id);
        $manifest->update(['manifest_status' => 'Received', 'receive_time' => now()]);
        return response()->json(['message' => 'Received']);
    }
}
