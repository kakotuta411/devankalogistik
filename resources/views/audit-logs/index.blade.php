@extends('layouts.app')
@section('page_header')<h1 class="h3 mb-0">Audit Log</h1>@endsection
@section('content')
@php
    function sortLink($col, $label) {
        $currentSort = request('sort', '');
        $currentDir = request('dir', 'asc');
        $dir = ($currentSort === $col && $currentDir === 'asc') ? 'desc' : 'asc';
        $icon = '';
        if ($currentSort === $col) {
            $icon = $currentDir === 'asc' ? ' ↑' : ' ↓';
        }
        $qs = http_build_query(array_merge(request()->query(), ['sort' => $col, 'dir' => $dir]));
        return '<a href="?' . $qs . '" class="text-decoration-none text-dark d-flex align-items-center gap-1">' . $label . $icon . '</a>';
    }
@endphp

<p class="text-body-secondary small mb-3">{{ $logs->total() }} log(s)</p>

<form method="GET" class="d-flex flex-wrap gap-2 align-items-end mb-3">
    <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" style="max-width:260px;" placeholder="Action / IP...">
    <select name="action_type" class="form-select form-select-sm" style="max-width:180px;">
        <option value="">Semua Tipe</option>
        @foreach($actionTypes as $t)
        <option value="{{ $t }}" {{ request('action_type') == $t ? 'selected' : '' }}>{{ $t }}</option>
        @endforeach
    </select>
    <select name="actor_role" class="form-select form-select-sm" style="max-width:180px;">
        <option value="">Semua Role</option>
        @foreach($roles as $r)
        <option value="{{ $r }}" {{ request('actor_role') == $r ? 'selected' : '' }}>{{ $r }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-sm btn-outline-primary"><i class="bi bi-funnel"></i> Filter</button>
</form>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{!! sortLink('created_at', 'Waktu') !!}</th>
                        <th>{!! sortLink('actor_role', 'Role') !!}</th>
                        <th>Aktor</th>
                        <th>{!! sortLink('action', 'Action') !!}</th>
                        <th>{!! sortLink('action_type', 'Tipe') !!}</th>
                        <th>{!! sortLink('ip_address', 'IP') !!}</th>
                        <th>Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td><small class="text-body-secondary">{{ $log->created_at?->format('d/m/Y H:i:s') }}</small></td>
                        <td><span class="badge text-bg-secondary">{{ $log->actor_role ?? '-' }}</span></td>
                        <td>{{ $log->actor?->name ?? '-' }}</td>
                        <td>{{ $log->action }}</td>
                        <td><span class="badge text-bg-info">{{ $log->action_type ?? '-' }}</span></td>
                        <td><small>{{ $log->ip_address ?? '-' }}</small></td>
                        <td>
                            @if($log->details)
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#detailModal{{ $log->id }}">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <div class="modal fade" id="detailModal{{ $log->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Detail Audit</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <pre class="bg-light p-3 rounded small">{{ json_encode($log->details, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <span class="text-body-secondary">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-body-secondary">Tidak ada data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="mt-3">{{ $logs->appends(request()->query())->links() }}</div>
@endsection