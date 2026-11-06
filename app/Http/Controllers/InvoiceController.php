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

        $invoices = Invoice::with('branch')
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
        return view('invoices.index', compact('invoices', 'branches'));
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
        $invoices = Invoice::with('branch')
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

    public function create()
    {
        $branchId = auth()->user()->branch_id;
        $availableShipments = Shipment::withoutGlobalScopes()
            ->where('origin_branch_id', $branchId)
            ->whereDoesntHave('invoices')
            ->get();
        $branches = \App\Models\Branch::all();
        return view('invoices.create', compact('availableShipments', 'branches'));
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
        $data['subtotal'] = 0;
        $data['amount_paid'] = $data['amount_paid'] ?? 0;

        // Calculate total_amount from manual costs
        $shippingCost = (float) ($data['shipping_cost'] ?? 0);
        $packagingCost = (float) ($data['packaging_cost'] ?? 0);
        $equipmentCharge = (float) ($data['equipment_charge'] ?? 0);
        $ppftz03 = (float) ($data['ppftz03'] ?? 0);
        $ppn = (float) ($data['ppn'] ?? 0);
        $pph = (float) ($data['pph'] ?? 0);
        $otherCosts = (float) ($data['other_costs'] ?? 0);
        $discount = (float) ($data['discount_value'] ?? 0);
        $data['discount'] = $discount;
        $data['total_amount'] = $shippingCost + $packagingCost + $equipmentCharge + $ppftz03 + $ppn + $pph + $otherCosts - $discount;

        $invoice = Invoice::create($data);
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
        $invoice->load(['branch', 'items.shipment.originBranch', 'items.shipment.destinationBranch', 'payments']);
        return view('invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        $invoice = Invoice::withoutGlobalScopes()->findOrFail($invoice->id);
        return view('invoices.edit', compact('invoice'));
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        $invoice = Invoice::withoutGlobalScopes()->findOrFail($invoice->id);
        $data = $request->validated();

        // Recalculate discount and total_amount
        $shippingCost = (float) ($data['shipping_cost'] ?? $invoice->shipping_cost ?? 0);
        $packagingCost = (float) ($data['packaging_cost'] ?? $invoice->packaging_cost ?? 0);
        $equipmentCharge = (float) ($data['equipment_charge'] ?? $invoice->equipment_charge ?? 0);
        $ppftz03 = (float) ($data['ppftz03'] ?? $invoice->ppftz03 ?? 0);
        $ppn = (float) ($data['ppn'] ?? $invoice->ppn ?? 0);
        $pph = (float) ($data['pph'] ?? $invoice->pph ?? 0);
        $otherCosts = (float) ($data['other_costs'] ?? $invoice->other_costs ?? 0);
        $discount = (float) ($data['discount_value'] ?? $invoice->discount_value ?? 0);
        $data['discount'] = $discount;
        $data['total_amount'] = $shippingCost + $packagingCost + $equipmentCharge + $ppftz03 + $ppn + $pph + $otherCosts - $discount;

        $invoice->update($data);
        AuditService::log('Update Invoice', 'UPDATE', $invoice);
        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice diperbarui');
    }

    public function recordPayment(Request $request, Invoice $invoice)
    {
        $invoice = Invoice::withoutGlobalScopes()->findOrFail($invoice->id);
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
        if ($invoice->amount_paid >= $invoice->total_amount && $invoice->total_amount > 0) {
            $invoice->invoice_status = 'Paid';
        }
        $invoice->save();
        AuditService::log('Record Payment', 'CREATE', $payment);
        return redirect()->route('invoices.show', $invoice)->with('success', 'Pembayaran dicatat');
    }

    public function destroy(Invoice $invoice)
    {
        $invoice = Invoice::withoutGlobalScopes()->findOrFail($invoice->id);
        AuditService::log('Delete Invoice', 'DELETE', $invoice);
        $invoice->delete();
        return redirect()->route('invoices.index')->with('success', 'Invoice dihapus');
    }

    public function markPaid($id)
    {
        $invoice = Invoice::withoutGlobalScopes()->findOrFail($id);
        $invoice->update(['invoice_status' => 'Paid']);
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
