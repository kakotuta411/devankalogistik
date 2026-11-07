<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use Illuminate\Http\Request;

class ShipmentController extends Controller
{
    public function index(Request $request)
    {
        $shipments = Shipment::with(['originBranch', 'destinationBranch'])
            ->when($request->status, fn($q, $s) => $q->where('shipment_status', $s))
            ->when($request->search, fn($q, $s) => $q->where('resi_no', 'like', "%$s%"))
            ->paginate($request->get('per_page', 15));
        return response()->json($shipments);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'destination_branch_id' => 'required|exists:branches,id',
            'sender_name' => 'nullable|string',
            'sender_phone' => 'nullable|string',
            'sender_address' => 'nullable|string',
            'receiver_name' => 'nullable|string',
            'receiver_phone' => 'nullable|string',
            'receiver_address' => 'nullable|string',
            'total_koli' => 'nullable|integer',
            'total_weight' => 'nullable|numeric',
            'service_type' => 'nullable|string',
        ]);
        $data['origin_branch_id'] = auth()->user()->branch_id;
        $data['resi_no'] = \App\Services\ResiGenerator::generate();
        $data['shipment_status'] = 'Created';
        $shipment = Shipment::create($data);
        return response()->json($shipment, 201);
    }

    public function show($id)
    {
        $shipment = Shipment::withoutGlobalScopes()->findOrFail($id);
        return response()->json($shipment);
    }

}
