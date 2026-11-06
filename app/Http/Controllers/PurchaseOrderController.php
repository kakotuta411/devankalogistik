<?php
namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\Customer;
use App\Models\Shipment;
use App\Models\Invoice;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    public function search(Request $request)
    {
        $customerId = $request->query('customer_id');
        $pos = PurchaseOrder::when($customerId, fn($q) => $q->where('customer_id', $customerId))
            ->get(['id', 'po_number', 'customer_id']);
        return response()->json($pos);
    }

    public function index(Request $request)
    {
        $pos = PurchaseOrder::with('customer')
            ->when($request->search, fn($q, $s) => $q->where('po_number', 'like', "%$s%"))
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return view('purchase-orders.index', compact('pos'));
    }

    public function create()
    {
        $customers = Customer::all();
        return view('purchase-orders.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'po_number' => 'required|string|max:100|unique:purchase_orders,po_number',
            'customer_id' => 'required|uuid|exists:customers,id',
            'total_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);
        $data['paid_amount'] = 0;
        $data['status'] = 'open';
        $po = PurchaseOrder::create($data);
        return redirect()->route('purchase-orders.show', $po)->with('success', 'PO dibuat');
    }

    public function show($id)
    {
        $po = PurchaseOrder::with('customer')->findOrFail($id);
        $shipments = Shipment::where('po_number', $po->po_number)->get();
        $invoices = Invoice::where('po_id', $po->id)->get();
        return view('purchase-orders.show', compact('po', 'shipments', 'invoices'));
    }

    public function edit($id)
    {
        $po = PurchaseOrder::findOrFail($id);
        $customers = Customer::all();
        return view('purchase-orders.edit', compact('po', 'customers'));
    }

    public function update(Request $request, $id)
    {
        $po = PurchaseOrder::findOrFail($id);
        $data = $request->validate([
            'po_number' => 'required|string|max:100|unique:purchase_orders,po_number,' . $id,
            'customer_id' => 'required|uuid|exists:customers,id',
            'total_amount' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|in:open,closed,cancelled',
            'notes' => 'nullable|string',
        ]);
        $po->update($data);
        return redirect()->route('purchase-orders.show', $po)->with('success', 'PO diperbarui');
    }

    public function destroy($id)
    {
        $po = PurchaseOrder::findOrFail($id);
        $po->delete();
        return redirect()->route('purchase-orders.index')->with('success', 'PO dihapus');
    }
}
