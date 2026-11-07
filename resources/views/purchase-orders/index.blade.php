@extends('layouts.app')
@section('title', 'Purchase Orders')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Purchase Orders</h4>
    </div>

    <!-- Cards -->
    <div class="row mb-3 g-2">
        <div class="col">
            <div class="card text-bg-primary">
                <div class="card-header text-center">PO Aktif</div>
                <div class="card-body text-center">
                    <h5>{{ $pos->where('status', '!=', 'paid')->count() }}</h5>
                    <small>{{ number_format($pos->where('status', '!=', 'paid')->sum('total_amount'), 0, ',', '.') }}</small>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card text-bg-secondary">
                <div class="card-header text-center">PO Draft</div>
                <div class="card-body text-center">
                    <h5>{{ $pos->where('status', 'draft')->count() }}</h5>
                    <small>{{ number_format($pos->where('status', 'draft')->sum('total_amount'), 0, ',', '.') }}</small>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card text-bg-warning">
                <div class="card-header text-center">PO Total</div>
                <div class="card-body text-center">
                    <h5>{{ $pos->count() }}</h5>
                    <small>{{ number_format($pos->sum('total_amount'), 0, ',', '.') }}</small>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card text-bg-success">
                <div class="card-header text-center">PO Lunas</div>
                <div class="card-body text-center">
                    <h5>{{ $pos->where('status', 'paid')->count() }}</h5>
                    <small>{{ number_format($pos->where('status', 'paid')->sum('total_amount'), 0, ',', '.') }}</small>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card text-bg-danger">
                <div class="card-header text-center">PO Tertunggak</div>
                <div class="card-body text-center">
                    <h5>{{ $pos->filter(fn($p) => $p->getRemainingAmount() > 0)->count() }}</h5>
                    <small>{{ number_format($pos->filter(fn($p) => $p->getRemainingAmount() > 0)->sum(fn($p) => $p->getRemainingAmount()), 0, ',', '.') }}</small>
                </div>
            </div>
        </div>
    </div>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari PO / customer..." value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select form-select-sm">
                <option value="">Semua Status</option>
                <option value="draft" {{ request('status')=='draft'?'selected':'' }}>Draft</option>
                <option value="active" {{ request('status')=='active'?'selected':'' }}>Aktif</option>
                <option value="partial" {{ request('status')=='partial'?'selected':'' }}>Partial</option>
                <option value="paid" {{ request('status')=='paid'?'selected':'' }}>Lunas</option>
                <option value="cancelled" {{ request('status')=='cancelled'?'selected':'' }}>Cancelled</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-sm btn-secondary">Filter</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover table-sm">
            <thead>
                <tr>
                    <th>No. PO</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Dibayar</th>
                    <th>Sisa</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pos as $po)
                <tr>
                    <td><a href="{{ route('purchase-orders.show', $po) }}">{{ $po->po_number }}</a></td>
                    <td>{{ $po->customer?->company_name ?? $po->customer?->name ?? '-' }}</td>
                    <td>{{ number_format($po->total_amount, 0, ',', '.') }}</td>
                    <td>{{ number_format($po->paid_amount, 0, ',', '.') }}</td>
                    <td>{{ number_format($po->getRemainingAmount(), 0, ',', '.') }}</td>
                    <td><span class="badge {{ $po->getStatusBadgeClass() }}">{{ ucfirst($po->status) }}</span></td>
                    <td>
                        <a href="{{ route('purchase-orders.show', $po) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted">Belum ada PO</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $pos->links() }}
</div>
@endsection