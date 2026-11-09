<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Invoice;
use App\Models\Shipment;
use App\Services\AuditService;
use App\Services\InvoiceGenerator;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $allowedSorts = ['invoice_no','customer_name','branch_id','invoice_status','subtotal','created_at'];
        $sortCol = in_array($request->sort, $allowedSorts) ? $request->sort : 'created_at';
        $sortDir = $request->has('sort') ? ($request->dir === 'desc' ? 'desc' : 'asc') : 'desc';

        $invoices = Invoice::with(['branch', 'childInvoices', 'payments', 'po'])
            ->when($request->status, fn($q, $s) => $q->where('invoice_status', $s))
            ->when($request->search, fn($q, $s) => $q->where(function($q2) use ($s) {
                $q2->where('invoice_no', 'like', "%$s%")
                   ->orWhere('customer_name', 'like', "%$s%");
            }))
            ->when($request->branch_id, fn($q, $b) => $q->where('branch_id', $b))
            ->when($request->filled('start_date') && $request->filled('end_date'), function($q) use ($request) {
                $start = Carbon::parse($request->start_date)->startOfDay();
                $end = Carbon::parse($request->end_date)->endOfDay();
                if ($start->diffInDays($end) > 90) {
                    $start = Carbon::today()->subDays(90)->startOfDay();
                    $end = Carbon::today()->endOfDay();
                    session()->flash('warning', 'Rentang tanggal maksimal 90 hari. Filter disesuaikan.');
                }
                $q->whereBetween('created_at', [$start->toDateTimeString(), $end->toDateTimeString()]);
            })
            ->orderBy($sortCol, $sortDir)
            ->paginate(10);
        $branches = \App\Models\Branch::all();

        // Card "Invoice Lunas": total = jumlah terbayar (amount_paid), bukan total_amount.
        // Parent + child invoice yang terbayar dijumlahkan, hasilnya = total PO.
        $paidCount = Invoice::where('invoice_status', 'Paid')->count();
        $paidAmount = Invoice::where('invoice_status', 'Paid')->sum('amount_paid');
        $pendingCount = Invoice::whereIn('invoice_status', ['Draft', 'Partial', 'Active'])->count();
        $pendingAmount = Invoice::whereIn('invoice_status', ['Draft', 'Partial', 'Active'])->sum('total_amount');

        // Koleksi untuk modal klik kartu
        $paidInvoices = Invoice::where('invoice_status', 'Paid')
            ->with(['po', 'payments'])->latest()->get();
        $pendingInvoices = Invoice::whereIn('invoice_status', ['Draft', 'Partial', 'Active'])
            ->with(['po', 'payments'])->latest()->get();

        return view('invoices.index', compact(
            'invoices', 'branches', 'paidCount', 'pendingCount', 'paidAmount', 'pendingAmount',
            'paidInvoices', 'pendingInvoices'
        ));
    }


    public function exportCsv(Request $request)
    {
        $invoices = $this->filteredInvoices($request);
        $filename = 'invoices_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        $callback = function () use ($invoices) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, ['No. Invoice', 'Pelanggan', 'Cabang', 'Status', 'Subtotal', 'Dibuat']);
            foreach ($invoices as $inv) {
                fputcsv($file, [
                    $inv->invoice_no,
                    $inv->customer_name,
                    $inv->branch?->city ?? '-',
                    $inv->invoice_status,
                    $inv->subtotal,
                    $inv->created_at?->format('d/m/Y H:i'),
                ]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf(Request $request)
    {
        $invoices = $this->filteredInvoices($request);
        $generated_at = now();
        $pdf = Pdf::loadView('print.invoice-list', compact('invoices', 'generated_at'));
        if ($request->get('preview')) {
            return $pdf->stream('invoices_' . date('Y-m-d_His') . '.pdf');
        }
        return $pdf->download('invoices_' . date('Y-m-d_His') . '.pdf');
    }

    private function filteredInvoices(Request $request)
    {
        $invoices = Invoice::with('branch', 'childInvoices')
            ->when($request->status, fn($q, $s) => $q->where('invoice_status', $s))
            ->when($request->search, fn($q, $s) => $q->where(function($q2) use ($s) {
                $q2->where('invoice_no', 'like', "%$s%")
                   ->orWhere('customer_name', 'like', "%$s%");
            }))
            ->when($request->branch_id, fn($q, $b) => $q->where('branch_id', $b));

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $start = \Carbon\Carbon::parse($request->start_date)->startOfDay();
            $end = \Carbon\Carbon::parse($request->end_date)->endOfDay();
            $invoices->whereBetween('created_at', [$start->toDateTimeString(), $end->toDateTimeString()]);
        }

        return $invoices->orderBy('created_at', 'desc')->get();
    }

    public function create(Request $request)
    {
        $branchId = auth()->user()->branch_id;
        $availableShipments = Shipment::withoutGlobalScopes()
            ->where('origin_branch_id', $branchId)
            ->whereDoesntHave('invoices', function($q) {
                $q->where('invoice_status', 'Paid');
            })
            ->get();
        $branches = \App\Models\Branch::all();
        $po = null;
        if ($request->has('po_id')) {
            $po = \App\Models\PurchaseOrder::with('customer')->find($request->po_id);
        }
        return view('invoices.create', compact('availableShipments', 'branches', 'po'));
    }

    public function store(StoreInvoiceRequest $request)
    {
        $data = $request->validated();
        $data['invoice_no'] = InvoiceGenerator::generate();
        $data['branch_id'] = auth()->user()->branch_id;
        $data['invoice_status'] = 'Draft';

        // Calculate totals from shipments (costs are manual, no rate auto-calc)
        $shipments = Shipment::withoutGlobalScopes()->whereIn('id', $data['shipment_ids'])->get();
        $data['total_koli'] = $shipments->sum('total_koli');
        $data['total_weight'] = $shipments->sum('total_weight');
        $data['amount_paid'] = $data['amount_paid'] ?? 0;

        // Auto-detect po_id from selected shipments if not provided
        if (empty($data['po_id'])) {
            $poNumbers = $shipments->pluck('po_number')->filter()->unique();
            if ($poNumbers->count() === 1) {
                $po = \App\Models\PurchaseOrder::where('po_number', $poNumbers->first())->first();
                if ($po) {
                    $data['po_id'] = $po->id;
                }
            }
        }

        // Calculate total_amount per formula:
        // total_layanan = (shipping_cost * total_weight) + packaging + equipment + other_costs
        // total = total_layanan - (total_layanan * diskon%) + ppftz03 + (total_layanan * ppn%) + (total_layanan * pph%)
        $shippingCost = (float) ($data['shipping_cost'] ?? 0);
        $packagingCost = (float) ($data['packaging_cost'] ?? 0);
        $equipmentCharge = (float) ($data['equipment_charge'] ?? 0);
        $ppftz03 = (float) ($data['ppftz03'] ?? 0);
        $ppnPct = (float) ($data['ppn'] ?? 0);
        $pphPct = (float) ($data['pph'] ?? 0);
        $otherCosts = (float) ($data['other_costs'] ?? 0);
        $diskonPct = (float) ($data['discount_value'] ?? 0);
        $totalWeight = (float) ($data['total_weight'] ?? 0);
        $totalLayanan = ($shippingCost * $totalWeight) + $packagingCost + $equipmentCharge + $otherCosts;
        $diskonAmt = $totalLayanan * ($diskonPct / 100);
        $ppnAmt = $totalLayanan * ($ppnPct / 100);
        $pphAmt = $totalLayanan * ($pphPct / 100);
        $data['discount'] = $diskonAmt;
        $data['total_amount'] = $totalLayanan - $diskonAmt + $ppftz03 + $ppnAmt + $pphAmt;
        $data['subtotal'] = $totalLayanan;

        $invoice = Invoice::create($data);

        // Update PO status to active and set total_amount from invoice if linked
        if (!empty($data['po_id'])) {
            $po = \App\Models\PurchaseOrder::find($data['po_id']);
            if ($po) {
                if ($po->status === 'draft') {
                    $po->status = 'active';
                }
                // Set PO total from this invoice when it's the only linked invoice
                $linkedCount = \App\Models\Invoice::where('po_id', $po->id)->count();
                if ($linkedCount <= 1) {
                    $po->total_amount = $data['total_amount'];
                }
                $po->save();
            }
        }

        foreach ($data['shipment_ids'] as $shipmentId) {
            $shipment = $shipments->firstWhere('id', $shipmentId);
            \App\Models\InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'shipment_id' => $shipmentId,
                'amount' => 0,
            ]);
        }

        AuditService::log('Create Invoice', 'CREATE', $invoice);
        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice dibuat');
    }

    public function show($id)
    {
        $invoice = Invoice::withoutGlobalScopes()->findOrFail($id);
        $invoice->load(['branch', 'items.shipment.originBranch', 'items.shipment.destinationBranch', 'payments', 'childInvoices', 'parentInvoice']);
        return view('invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        $invoice = Invoice::withoutGlobalScopes()->findOrFail($invoice->id);
        if ($invoice->childInvoices()->exists()) {
            return redirect()->route('invoices.show', $invoice)->with('error', 'Invoice sudah dipecah (partial payment). Tidak bisa diedit.');
        }
        return view('invoices.edit', compact('invoice'));
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        $invoice = Invoice::withoutGlobalScopes()->findOrFail($invoice->id);
        if ($invoice->childInvoices()->exists()) {
            return redirect()->route('invoices.show', $invoice)->with('error', 'Invoice sudah dipecah (partial payment). Tidak bisa diedit.');
        }
        $data = $request->validated();

        // Recalculate total_amount per formula
        $shippingCost = (float) ($data['shipping_cost'] ?? $invoice->shipping_cost ?? 0);
        $packagingCost = (float) ($data['packaging_cost'] ?? $invoice->packaging_cost ?? 0);
        $equipmentCharge = (float) ($data['equipment_charge'] ?? $invoice->equipment_charge ?? 0);
        $ppftz03 = (float) ($data['ppftz03'] ?? $invoice->ppftz03 ?? 0);
        $ppnPct = (float) ($data['ppn'] ?? $invoice->ppn ?? 0);
        $pphPct = (float) ($data['pph'] ?? $invoice->pph ?? 0);
        $otherCosts = (float) ($data['other_costs'] ?? $invoice->other_costs ?? 0);
        $diskonPct = (float) ($data['discount_value'] ?? $invoice->discount_value ?? 0);
        $totalWeight = (float) ($invoice->total_weight ?? 0);
        $totalLayanan = ($shippingCost * $totalWeight) + $packagingCost + $equipmentCharge + $otherCosts;
        $diskonAmt = $totalLayanan * ($diskonPct / 100);
        $ppnAmt = $totalLayanan * ($ppnPct / 100);
        $pphAmt = $totalLayanan * ($pphPct / 100);
        $data['discount'] = $diskonAmt;
        $data['total_amount'] = $totalLayanan - $diskonAmt + $ppftz03 + $ppnAmt + $pphAmt;
        $data['subtotal'] = $totalLayanan;

        $invoice->update($data);

        // Sync PO total when invoice is updated (if sole invoice linked to PO)
        if ($invoice->po_id) {
            $po = \App\Models\PurchaseOrder::find($invoice->po_id);
            if ($po) {
                $linkedCount = \App\Models\Invoice::where('po_id', $po->id)->count();
                if ($linkedCount <= 1) {
                    $po->total_amount = $data['total_amount'];
                    $po->save();
                }
            }
        }

        AuditService::log('Update Invoice', 'UPDATE', $invoice);
        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice diperbarui');
    }

    public function recordPayment(Request $request, Invoice $invoice)
    {
        $invoice = Invoice::withoutGlobalScopes()->findOrFail($invoice->id);
        if ($invoice->childInvoices()->exists()) {
            return redirect()->route('invoices.show', $invoice)->with('error', 'Invoice sudah dipecah (partial payment). Pembayaran dilanjutkan di invoice baru.');
        }
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);
        $payment = \App\Models\InvoicePayment::create([
            'invoice_id' => $invoice->id,
            'amount' => $data['amount'],
            'payment_date' => $data['payment_date'],
            'payment_method' => $data['payment_method'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);
        $invoice->amount_paid = $invoice->amount_paid + $data['amount'];
        $remaining = $invoice->total_amount - $invoice->amount_paid;
        if ($invoice->amount_paid >= $invoice->total_amount && $invoice->total_amount > 0) {
            $invoice->invoice_status = 'Paid';
            // If this invoice has a parent, update parent status to Paid (all children paid)
            if ($invoice->parent_invoice_id) {
                $parent = \App\Models\Invoice::find($invoice->parent_invoice_id);
                if ($parent && $parent->invoice_status !== 'Paid') {
                    // Check if all siblings (including this one) are paid
                    $unpaidSiblings = \App\Models\Invoice::where('parent_invoice_id', $parent->id)
                        ->where('id', '!=', $invoice->id)
                        ->whereNotIn('invoice_status', ['Paid', 'Cancelled'])
                        ->count();
                    if ($unpaidSiblings === 0) {
                        $parent->invoice_status = 'Paid';
                        $parent->save();
                    }
                }
            }
            // Update PO status if linked - only mark paid if all invoices for this PO are paid
            if ($invoice->po_id) {
                $po = \App\Models\PurchaseOrder::find($invoice->po_id);
                if ($po) {
                    // Recalculate paid from all invoices' amount_paid for this PO
                    $po->paid_amount = \App\Models\Invoice::where('po_id', $po->id)
                        ->whereNotIn('invoice_status', ['Cancelled'])
                        ->sum('amount_paid');
                    $unpaidCount = \App\Models\Invoice::where('po_id', $po->id)
                        ->whereNotIn('invoice_status', ['Paid', 'Cancelled'])
                        ->count();
                    if ($unpaidCount === 0 && $po->total_amount > 0) {
                        $po->status = 'paid';
                    } else {
                        $po->status = 'partial';
                    }
                    $po->save();
                }
            }
        } elseif ($remaining > 0) {
            // Parent invoice becomes Paid (paid amount recorded), remainder goes to child invoice
            $invoice->invoice_status = 'Paid';
            // Auto-create new invoice for remaining balance
            $newInvoice = Invoice::create([
                'invoice_no' => \App\Services\InvoiceGenerator::generate(),
                'branch_id' => $invoice->branch_id,
                'customer_id' => $invoice->customer_id,
                'po_id' => $invoice->po_id,
                'parent_invoice_id' => $invoice->parent_invoice_id ?: $invoice->id,
                'customer_name' => $invoice->customer_name,
                'customer_phone' => $invoice->customer_phone,
                'customer_address' => $invoice->customer_address,
                'customer_email' => $invoice->customer_email,
                'customer_province_code' => $invoice->customer_province_code,
                'customer_regency_code' => $invoice->customer_regency_code,
                'customer_district_code' => $invoice->customer_district_code,
                'customer_village_code' => $invoice->customer_village_code,
                'subtotal' => $invoice->subtotal,
                'total_koli' => $invoice->total_koli,
                'total_weight' => $invoice->total_weight,
                'shipping_cost' => $invoice->shipping_cost,
                'packaging_cost' => $invoice->packaging_cost,
                'equipment_charge' => $invoice->equipment_charge,
                'ppftz03' => $invoice->ppftz03,
                'ppn' => $invoice->ppn,
                'pph' => $invoice->pph,
                'discount_type' => $invoice->discount_type,
                'discount_value' => $invoice->discount_value,
                'other_costs' => $invoice->other_costs,
                'discount' => $invoice->discount,
                'amount_paid' => 0,
                'total_amount' => $remaining,
                'invoice_status' => 'Draft',
                'invoice_date' => now(),
                'due_date' => $invoice->due_date,
                'notes' => 'Sisa dari invoice ' . $invoice->invoice_no,
            ]);
            // Copy invoice items
            foreach ($invoice->items as $item) {
                \App\Models\InvoiceItem::create([
                    'invoice_id' => $newInvoice->id,
                    'shipment_id' => $item->shipment_id,
                    'amount' => 0,
                ]);
            }
            // Update PO paid_amount
            if ($invoice->po_id) {
                $po = \App\Models\PurchaseOrder::find($invoice->po_id);
                if ($po) {
                    // Recalculate paid from all invoices' amount_paid for this PO
                    $po->paid_amount = \App\Models\Invoice::where('po_id', $po->id)
                        ->whereNotIn('invoice_status', ['Cancelled'])
                        ->sum('amount_paid');
                    $po->status = 'partial';
                    $po->save();
                }
            }
        }
        $invoice->save();
        AuditService::log('Record Payment', 'CREATE', $payment);
        return redirect()->route('invoices.show', $invoice)->with('success', 'Pembayaran dicatat');
    }

    public function destroy(Invoice $invoice)
    {
        $invoice = Invoice::withoutGlobalScopes()->findOrFail($invoice->id);
        $poId = $invoice->po_id;
        $paidAmount = (float) $invoice->amount_paid;
        AuditService::log('Delete Invoice', 'DELETE', $invoice);
        $invoice->delete();

        if ($poId) {
            $po = \App\Models\PurchaseOrder::find($poId);
            if ($po) {
                $remaining = \App\Models\Invoice::where('po_id', $poId)->get();
                if ($remaining->isEmpty()) {
                    $po->total_amount = 0;
                    $po->paid_amount = 0;
                    $po->status = 'draft';
                } else {
                    $po->paid_amount = max(0, (float)$po->paid_amount - $paidAmount);
                }
                $po->save();
            }
        }

        return redirect()->route('invoices.index')->with('success', 'Invoice dihapus');
    }

    public function markPaid($id)
    {
        $invoice = Invoice::withoutGlobalScopes()->findOrFail($id);
        $invoice->update(['invoice_status' => 'Paid', 'amount_paid' => $invoice->total_amount]);
        // Sync PO status
        if ($invoice->po_id) {
            $po = \App\Models\PurchaseOrder::find($invoice->po_id);
            if ($po) {
                // Recalculate paid from all invoices' amount_paid for this PO
                $po->paid_amount = \App\Models\Invoice::where('po_id', $po->id)
                    ->whereNotIn('invoice_status', ['Cancelled'])
                    ->sum('amount_paid');
                $unpaidCount = \App\Models\Invoice::where('po_id', $po->id)
                    ->whereNotIn('invoice_status', ['Paid', 'Cancelled'])
                    ->count();
                if ($unpaidCount === 0 && $po->total_amount > 0) {
                    $po->status = 'paid';
                } else {
                    $po->status = 'partial';
                }
                $po->save();
            }
        }
        AuditService::log('Mark Invoice Paid', 'MARK_PAID', $invoice);
        return back()->with('success', 'Invoice ditandai lunas');
    }

    public function markCancelled($id)
    {
        $invoice = Invoice::withoutGlobalScopes()->findOrFail($id);
        $invoice->update(['invoice_status' => 'Cancelled']);
        AuditService::log('Mark Invoice Cancelled', 'MARK_CANCELLED', $invoice);
        return back()->with('success', 'Invoice dibatalkan');
    }

    public function addItem(Request $request, $id)
    {
        $invoice = Invoice::withoutGlobalScopes()->findOrFail($id);
        $shipment = Shipment::withoutGlobalScopes()->findOrFail($request->shipment_id);
        $invoice->shipments()->syncWithoutDetaching([$shipment->id]);
        AuditService::log('Add Shipment to Invoice', 'ADD_ITEM', $invoice);
        return back()->with('success', 'Shipment ditambahkan ke invoice');
    }
}
