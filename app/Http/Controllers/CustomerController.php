<?php
namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function search(Request $request)
    {
        $q = $request->query('q', '');
        $customers = Customer::where('name', 'like', "%{$q}%")
            ->orWhere('company_name', 'like', "%{$q}%")
            ->get(['id', 'name', 'company_name', 'phone', 'email', 'address',
                   'province_code', 'regency_code', 'district_code', 'village_code']);
        return response()->json($customers);
    }

    public function shipmentsByCustomer(Request $request, $customerId)
    {
        // Find PO numbers that have any shipment assigned to any active (non-cancelled) invoice
        $activeInvoiceIds = \App\Models\Invoice::where('invoice_status', '!=', 'Cancelled')
            ->pluck('id')
            ->toArray();

        $excludedPoNumbers = \App\Models\InvoiceItem::whereIn('invoice_id', $activeInvoiceIds)
            ->join('shipments', 'invoice_items.shipment_id', '=', 'shipments.id')
            ->whereNotNull('shipments.po_number')
            ->pluck('shipments.po_number')
            ->unique()
            ->toArray();

        $shipments = \App\Models\Shipment::where('customer_id', $customerId)
            ->where(function ($q) use ($excludedPoNumbers) {
                // Shipment tanpa PO tetap tampil (grup "Tanpa PO"); sisanya harus
                // bukan PO yang sudah punya invoice aktif.
                $q->whereNull('po_number')->orWhereNotIn('po_number', $excludedPoNumbers);
            })
            ->select('id', 'resi_no', 'po_number', 'sender_name', 'receiver_name', 'total_koli', 'total_weight', 'service_type', 'shipment_status')
            ->orderBy('po_number')
            ->get();

        // Group by po_number
        $grouped = $shipments->groupBy('po_number')->map(function ($items, $po) {
            return [
                'po_number' => $po ?: '(Tanpa PO)',
                'shipments' => $items->values(),
            ];
        })->values();

        return response()->json($grouped);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'phone' => 'nullable|digits_between:8,20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'province_code' => 'nullable|string|max:100',
            'regency_code' => 'nullable|string|max:100',
            'district_code' => 'nullable|string|max:100',
            'village_code' => 'nullable|string|max:100',
        ]);
        $customer = Customer::create($data);
        return response()->json($customer);
    }

    public function checkPoNumber(Request $request)
    {
        $poNumber = $request->input('po_number');
        $customerId = $request->input('customer_id');

        if (empty($poNumber)) {
            return response()->json(['exists' => false]);
        }

        $existing = \App\Models\Shipment::where('po_number', $poNumber)
            ->whereNotNull('customer_id')
            ->where('customer_id', '!=', $customerId)
            ->with('customer:id,name')
            ->first();

        if ($existing) {
            return response()->json([
                'exists' => true,
                'customer_name' => $existing->customer?->name ?? 'Unknown',
                'resi_no' => $existing->resi_no,
            ]);
        }

        return response()->json(['exists' => false]);
    }
}
