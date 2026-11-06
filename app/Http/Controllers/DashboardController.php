<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\Manifest;
use App\Models\Shipment;
use App\Models\Branch;
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
            'recent_audits' => AuditLog::latest()->limit(10)->get(),
            'date_range' => ['start' => $startStr, 'end' => $endStr, 'start_date' => $start->toDateString(), 'end_date' => $end->toDateString()],
        ];
        return view('dashboard', compact('stats'));
    }
}
