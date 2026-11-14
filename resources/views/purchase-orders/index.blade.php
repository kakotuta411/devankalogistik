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
            <div class="card text-bg-primary" role="button" style="cursor:pointer" data-bs-toggle="modal" data-bs-target="#poActiveModal">
                <div class="card-header text-center">PO Aktif</div>
                <div class="card-body text-center">
                    <h5>{{ $activePos->count() }}</h5>
                    <small>{{ number_format($activePos->sum('total_amount'), 0, ',', '.') }}</small>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card text-bg-secondary" role="button" style="cursor:pointer" data-bs-toggle="modal" data-bs-target="#poDraftModal">
                <div class="card-header text-center">PO Draft</div>
                <div class="card-body text-center">
                    <h5>{{ $draftPos->count() }}</h5>
                    <small>{{ number_format($draftPos->sum('total_amount'), 0, ',', '.') }}</small>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card text-bg-warning" role="button" style="cursor:pointer" data-bs-toggle="modal" data-bs-target="#poAllModal">
                <div class="card-header text-center">PO Total</div>
                <div class="card-body text-center">
                    <h5>{{ $allPos->count() }}</h5>
                    <small>{{ number_format($allPos->sum('total_amount'), 0, ',', '.') }}</small>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card text-bg-success" role="button" style="cursor:pointer" data-bs-toggle="modal" data-bs-target="#poPaidModal">
                <div class="card-header text-center">PO Lunas</div>
                <div class="card-body text-center">
                    <h5>{{ $paidPos->count() }}</h5>
                    <small>{{ number_format($paidPos->sum('total_amount'), 0, ',', '.') }}</small>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card text-bg-danger" role="button" style="cursor:pointer" data-bs-toggle="modal" data-bs-target="#poOverdueModal">
                <div class="card-header text-center">PO Tertunggak</div>
                <div class="card-body text-center">
                    <h5>{{ $overduePos->count() }}</h5>
                    <small>{{ number_format($overduePos->sum(fn($p) => $p->getRemainingAmount()), 0, ',', '.') }}</small>
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
        <div class="col-md-auto ms-auto">
            <div class="btn-group btn-group-sm">
                <button type="button" id="exportPdfBtn" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#pdfPreviewModal"><i class="bi bi-file-pdf"></i> Export PDF</button>
                <a href="#" id="exportCsvBtn" class="btn btn-outline-success"><i class="bi bi-file-spreadsheet"></i> Export Excel</a>
            </div>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover table-sm">
            <thead class="table-light">
                <tr>
                    <th class="text-center">Customer</th>
                    <th class="text-center">No. PO</th>
                    <th class="text-center">Total</th>
                    <th class="text-center text-end">Dibayar</th>
                    <th class="text-center text-end">Sisa</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Dibuat</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pos as $po)
                <tr>
                    <td>{{ $po->customer?->company_name ?? $po->customer?->name ?? '-' }}</td>
                    <td><a href="{{ route('purchase-orders.show', $po) }}">{{ $po->po_number }}</a></td>
                    <td>{{ number_format($po->total_amount, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($po->paid_amount, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($po->getRemainingAmount(), 0, ',', '.') }}</td>
                    <td><span class="badge {{ $po->getStatusBadgeClass() }}">{{ ucfirst($po->status) }}</span></td>
                    <td><small class="text-body-secondary" data-uts="{{ $po->created_at?->getTimestamp() }}">{{ $po->created_at?->format('d/m/Y H:i') }}</small></td>
                    <td>
                        <a href="{{ route('purchase-orders.show', $po) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted">Belum ada PO</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $pos->links() }}
</div>

<!-- PO Modals -->
@php
    $poModals = [
        'poAllModal' => ['title' => 'Semua PO', 'items' => $allPos, 'badge' => 'secondary'],
        'poActiveModal' => ['title' => 'PO Aktif', 'items' => $activePos, 'badge' => 'primary'],
        'poDraftModal' => ['title' => 'PO Draft', 'items' => $draftPos, 'badge' => 'secondary'],
        'poPaidModal' => ['title' => 'PO Lunas', 'items' => $paidPos, 'badge' => 'success'],
        'poOverdueModal' => ['title' => 'PO Tertunggak', 'items' => $overduePos, 'badge' => 'danger'],
    ];
@endphp
@foreach($poModals as $modalId => $modal)
<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $modal['title'] }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center">Customer</th>
                                <th class="text-center">No. PO</th>
                                <th class="text-center">Total</th>
                                <th class="text-center text-end">Dibayar</th>
                                <th class="text-center text-end">Sisa</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Dibuat</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($modal['items'] as $po)
                            <tr>
                                <td>{{ $po->customer?->company_name ?? $po->customer?->name ?? '-' }}</td>
                                <td><a href="{{ route('purchase-orders.show', $po) }}" class="fw-medium text-decoration-none">{{ $po->po_number }}</a></td>
                                <td>{{ number_format($po->total_amount, 0, ',', '.') }}</td>
                                <td class="text-end">{{ number_format($po->paid_amount, 0, ',', '.') }}</td>
                                <td class="text-end">{{ number_format($po->getRemainingAmount(), 0, ',', '.') }}</td>
                                <td><span class="badge {{ $po->getStatusBadgeClass() }}">{{ ucfirst($po->status) }}</span></td>
                                <td><small class="text-body-secondary" data-uts="{{ $po->created_at?->getTimestamp() }}">{{ $po->created_at?->format('d/m/Y H:i') }}</small></td>
                                <td class="text-end"><a href="{{ route('purchase-orders.show', $po) }}" class="btn btn-sm btn-outline-secondary">Detail</a></td>
                            </tr>
                            @empty
                            <tr><td colspan="8" class="text-center text-muted">Tidak ada data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endforeach

<!-- PDF Preview Modal -->
<div class="modal fade" id="pdfPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" style="height:90vh;">
        <div class="modal-content" style="height:100%;">
            <div class="modal-header">
                <h5 class="modal-title">Preview PDF</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="flex:1;overflow:hidden;">
                <iframe id="pdfPreviewFrame" src="" style="width:100%;height:100%;border:none;"></iframe>
            </div>
            <div class="modal-footer">
                <a id="pdfDownloadBtn" href="#" class="btn btn-danger" target="_blank"><i class="bi bi-download"></i> Download PDF</a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var exportCsvBtn = document.getElementById('exportCsvBtn');
    if (exportCsvBtn) {
        var qs = window.location.search;
        if (qs && qs.charAt(0) === '?') qs = qs.substring(1);
        exportCsvBtn.href = '/purchase-orders/export/csv' + (qs ? '?' + qs : '');
    }
    var pdfPreviewModal = document.getElementById('pdfPreviewModal');
    if (pdfPreviewModal) {
        pdfPreviewModal.addEventListener('show.bs.modal', function() {
            var qs = window.location.search;
            if (qs && qs.charAt(0) === '?') qs = qs.substring(1);
            var previewUrl = '/purchase-orders/export/pdf?' + qs + (qs ? '&' : '') + 'preview=1';
            var downloadUrl = '/purchase-orders/export/pdf?' + qs;
            document.getElementById('pdfPreviewFrame').src = previewUrl;
            document.getElementById('pdfDownloadBtn').href = downloadUrl;
        });
        pdfPreviewModal.addEventListener('hidden.bs.modal', function() {
            document.getElementById('pdfPreviewFrame').src = '';
        });
    }
});
</script>
@endsection