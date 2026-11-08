<?php
namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $allowedSorts = ['action', 'action_type', 'actor_role', 'ip_address', 'created_at'];
        $sortCol = in_array($request->sort, $allowedSorts) ? $request->sort : 'created_at';
        $sortDir = $request->has('sort') ? ($request->dir === 'asc' ? 'asc' : 'desc') : 'desc';

        $query = AuditLog::with('actor');

        $query->when($request->search, fn($q, $s) => $q->where(function($q2) use ($s) {
            $q2->where('action', 'like', "%$s%")
               ->orWhere('action_type', 'like', "%$s%")
               ->orWhere('ip_address', 'like', "%$s%");
        }))
        ->when($request->action_type, fn($q, $t) => $q->where('action_type', $t))
        ->when($request->actor_role, fn($q, $r) => $q->where('actor_role', $r))
        ->orderBy($sortCol, $sortDir);

        $logs = $query->paginate(20)->withQueryString();

        $actionTypes = AuditLog::distinct()->pluck('action_type')->filter()->sort()->values();
        $roles = AuditLog::distinct()->pluck('actor_role')->filter()->sort()->values();

        return view('audit-logs.index', compact('logs', 'actionTypes', 'roles'));
    }
}