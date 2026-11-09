<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\Manifest;
use App\Models\Shipment;
use App\Models\Branch;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $sortCol = $request->sort ?: 'created_at';
        $sortDir = $request->dir === 'asc' ? 'asc' : 'desc';

        // Date range filter — default this month, max 90 days
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();
            if ($start->diffInDays($end) > 90) {
                $start = Carbon::today()->subDays(90)->startOfDay();
                $end = Carbon::today()->endOfDay();
                session()->flash('warning', 'Rentang tanggal maksimal 90 hari. Filter disesuaikan.');
            }
        } else {
            $start = Carbon::today()->startOfMonth();
            $end = Carbon::today()->endOfDay();
        }

        $dateFilter = ['>=', '<='];
        $startStr = $start->toDateTimeString();
        $endStr = $end->toDateTimeString();

        // Validate sort column to avoid SQL injection
        $allowedSorts = ['resi_no','origin_city','destination_city','shipment_status','created_at'];
        if (!in_array($sortCol, $allowedSorts)) { $sortCol = 'created_at'; }

        $recentShipments = Shipment::with(['originBranch', 'destinationBranch'])
            ->whereBetween('created_at', [$startStr, $endStr])
            ->limit(10);
        if ($sortCol === 'origin_city') {
            $recentShipments->orderByRaw('(SELECT city FROM branches WHERE branches.id = shipments.origin_branch_id) ' . $sortDir);
        } elseif ($sortCol === 'destination_city') {
            $recentShipments->orderByRaw('(SELECT city FROM branches WHERE branches.id = shipments.destination_branch_id) ' . $sortDir);
        } else {
            $recentShipments->orderBy($sortCol, $sortDir);
        }

        $stats = [
            'shipments_today' => Shipment::whereBetween('created_at', [$startStr, $endStr])->count(),
            'manifests_today' => Manifest::whereBetween('created_at', [$startStr, $endStr])->count(),
            'branches' => Branch::count(),
            'recent_shipments' => $recentShipments->get(),
            'recent_manifests' => Manifest::whereBetween('created_at', [$startStr, $endStr])->latest()->limit(10)->get(),
            'recent_invoices' => Invoice::whereBetween('created_at', [$startStr, $endStr])->latest()->limit(10)->get(),
            'invoices_today' => Invoice::whereBetween('created_at', [$startStr, $endStr])->count(),
            // Card "Invoice Lunas": total = jumlah terbayar (amount_paid), konsisten dgn menu Invoice
            'invoices_paid' => Invoice::where('invoice_status', 'Paid')->count(),
            'invoices_pending' => Invoice::whereIn('invoice_status', ['Draft', 'Partial', 'Active'])->count(),
            'invoices_paid_amount' => Invoice::where('invoice_status', 'Paid')->sum('amount_paid'),
            'invoices_pending_amount' => Invoice::whereIn('invoice_status', ['Draft', 'Partial', 'Active'])->sum('total_amount'),
            'recent_audits' => AuditLog::latest()->limit(10)->get(),
            'date_range' => ['start' => $startStr, 'end' => $endStr, 'start_date' => $start->toDateString(), 'end_date' => $end->toDateString()],

            // Tabel Invoice Pending: top 10 sisa tunggakan terbesar
            'pending_invoices' => Invoice::withoutGlobalScopes()
                ->whereNotIn('invoice_status', ['Paid', 'Cancelled'])
                ->with('customer')
                ->get()
                ->map(function ($inv) {
                    $total = (float) $inv->displayTotal();
                    $paid = (float) ($inv->amount_paid ?? 0);
                    $inv->progress_pct = $total > 0 ? min(100, round($paid / $total * 100)) : 0;
                    $inv->sisa_tunggakan = max(0, $total - $paid);
                    return $inv;
                })
                ->sortByDesc('sisa_tunggakan')
                ->take(10),

            // Tabel PO Pending: top 10 sisa tunggakan terbesar
            'pending_pos' => PurchaseOrder::withoutGlobalScopes()
                ->whereNotIn('status', ['paid', 'cancelled'])
                ->with('customer')
                ->get()
                ->map(function ($po) {
                    $total = (float) $po->total_amount;
                    $paid = (float) $po->paid_amount;
                    $po->progress_pct = $total > 0 ? min(100, round($paid / $total * 100)) : 0;
                    $po->sisa_tunggakan = max(0, $po->getRemainingAmount());
                    return $po;
                })
                ->sortByDesc('sisa_tunggakan')
                ->take(10),
        ];
        return view('dashboard', compact('stats'));
    }
}
