<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\PurchaseOrder;
use App\Models\Shipment;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        // Sync PO statuses before display
        PurchaseOrder::whereNotIn('status', ['cancelled', 'draft'])->get()->each(function ($po) {
            // Recalculate paid_amount from all linked invoices
            $po->paid_amount = \App\Models\Invoice::where('po_id', $po->id)
                ->whereNotIn('invoice_status', ['Cancelled'])
                ->sum('amount_paid');
            if ($po->paid_amount >= $po->total_amount && $po->total_amount > 0) {
                $po->status = 'paid';
            } elseif ($po->paid_amount > 0) {
                $po->status = 'partial';
            } else {
                $po->status = 'active';
            }
            $po->save();
        });

        $query = PurchaseOrder::with('customer');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('po_number', 'like', "%{$s}%")
                  ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$s}%")
                      ->orWhere('company_name', 'like', "%{$s}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $pos = $query->latest()->paginate(10);

        // Koleksi untuk kartu + modal (semua PO, bukan hanya halaman aktif)
        $allPos = PurchaseOrder::with('customer')->latest()->get();
        $activePos = $allPos->where('status', '!=', 'paid');
        $draftPos = $allPos->where('status', 'draft');
        $paidPos = $allPos->where('status', 'paid');
        $overduePos = $allPos->filter(fn($p) => $p->getRemainingAmount() > 0);

        return view('purchase-orders.index', compact(
            'pos', 'allPos', 'activePos', 'draftPos', 'paidPos', 'overduePos'
        ));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        return view('purchase-orders.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'po_number' => 'required|string|max:100|unique:purchase_orders,po_number',
            'customer_id' => 'required|uuid|exists:customers,id',
            'notes' => 'nullable|string',
        ]);

        $po = PurchaseOrder::create([
            'po_number' => $validated['po_number'],
            'customer_id' => $validated['customer_id'],
            'total_amount' => 0,
            'paid_amount' => 0,
            'status' => 'draft',
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('purchase-orders.show', $po)->with('success', 'PO berhasil dibuat');
    }

    public function show($id)
    {
        $po = PurchaseOrder::with('customer')->findOrFail($id);
        // Sync PO status based on payment
        if ($po->status !== 'cancelled' && $po->status !== 'draft') {
            // Recalculate paid_amount from all linked invoices
            $po->paid_amount = \App\Models\Invoice::where('po_id', $po->id)
                ->whereNotIn('invoice_status', ['Cancelled'])
                ->sum('amount_paid');
            if ($po->paid_amount >= $po->total_amount && $po->total_amount > 0) {
                $po->status = 'paid';
            } elseif ($po->paid_amount > 0) {
                $po->status = 'partial';
            } else {
                $po->status = 'active';
            }
            $po->save();
        }
        $shipments = Shipment::withoutGlobalScopes()
            ->where('po_number', $po->po_number)
            ->with(['originBranch', 'destinationBranch'])
            ->get();
        $invoices = $po->invoices()->with('branch')->get();
        return view('purchase-orders.show', compact('po', 'shipments', 'invoices'));
    }

    public function edit($id)
    {
        $po = PurchaseOrder::findOrFail($id);
        $customers = Customer::orderBy('name')->get();
        return view('purchase-orders.edit', compact('po', 'customers'));
    }

    public function update(Request $request, $id)
    {
        $po = PurchaseOrder::findOrFail($id);
        $validated = $request->validate([
            'po_number' => 'required|string|max:100|unique:purchase_orders,po_number,' . $id,
            'customer_id' => 'required|uuid|exists:customers,id',
            'notes' => 'nullable|string',
            'status' => 'nullable|string|in:draft,active,paid,partial,cancelled',
        ]);

        $po->update($validated);

        return redirect()->route('purchase-orders.show', $po)->with('success', 'PO berhasil diupdate');
    }

    public function destroy($id)
    {
        $po = PurchaseOrder::findOrFail($id);
        $po->delete();
        return redirect()->route('purchase-orders.index')->with('success', 'PO berhasil dihapus');
    }
}