@extends('layouts.app')
@section('page_header')<h1 class="h3 mb-0">Manifests</h1>@endsection
@section('toolbar')
    <div class="btn-group">
        @if(auth()->user()?->canWrite())
        @if(auth()->user()->canManageManifests())
        <a href="/manifests/create" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i> Buat Manifest</a>
        @endif
        @endif
    </div>
@endsection
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

<p class="text-body-secondary small mb-3">{{ $manifests->total() }} manifest(s)</p>

<form method="GET" class="d-flex flex-wrap gap-1 align-items-end mb-3">
    <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" style="max-width:210px;" placeholder="No manifest / kendaraan / driver...">
    <select name="status" class="form-select form-select-sm" style="max-width:130px;">
        <option value="">Semua Status</option>
        @foreach(['Draft','Dispatched','Received','Completed'] as $st)
        <option value="{{ $st }}" {{ request('status') == $st ? 'selected' : '' }}>{{ $st }}</option>
        @endforeach
    </select>
    <select name="branch_id" class="form-select form-select-sm" style="max-width:170px;">
        <option value="">Semua Cabang</option>
        @foreach($branches ?? [] as $b)
        <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->branch_code }} — {{ $b->name }}</option>
        @endforeach
    </select>
        <div class="input-group input-group-sm" style="max-width:210px;">
        <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control form-control-sm" placeholder="Start" title="Max 90 hari">
        <span class="input-group-text">to</span>
        <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control form-control-sm" placeholder="End" title="Max 90 hari">
    </div>
        <div class="btn-group btn-group-sm">
        <button type="button" class="btn btn-outline-secondary" id="presetBtn" data-bs-toggle="dropdown">
            <i class="bi bi-funnel"></i> Presets
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item preset-opt" href="#" data-days="7">Last 7 Days</a></li>
            <li><a class="dropdown-item preset-opt" href="#" data-days="30">Last 30 Days</a></li>
            <li><a class="dropdown-item preset-opt" href="#" data-days="this_month">This Month</a></li>
            <li><a class="dropdown-item preset-opt" href="#" data-days="0">All Time (max 90d)</a></li>
        </ul>
    </div>
    <button type="submit" class="btn btn-sm btn-outline-primary"><i class="bi bi-check2"></i> Apply</button>
    <div class="ms-auto d-flex gap-1">
        <button type="button" id="exportPdfBtn" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#pdfPreviewModal"><i class="bi bi-file-pdf"></i> Export PDF</button>
        <a href="/manifests/export/csv{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" id="exportCsvBtn" class="btn btn-sm btn-outline-success"><i class="bi bi-file-spreadsheet"></i> Export Excel</a>
    </div>
</form>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{!! sortLink('manifest_no', 'No Manifest') !!}</th>
                        <th>{!! sortLink('origin_city', 'Asal') !!}</th>
                        <th>{!! sortLink('destination_city', 'Tujuan') !!}</th>
                        <th>{!! sortLink('manifest_status', 'Status') !!}</th>
                        <th>{!! sortLink('vehicle_no', 'Kendaraan') !!}</th>
                        <th>{!! sortLink('driver_name', 'Driver') !!}</th>
                        <th>{!! sortLink('created_at', 'Dibuat') !!}</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($manifests as $m)
                    <tr>
                        <td><a href="/manifests/{{ $m->id }}" class="fw-medium text-decoration-none">{{ $m->manifest_no }}</a></td>
                        <td>{{ $m->originBranch?->city ?? '-' }}</td>
                        <td>{{ $m->destinationBranch?->city ?? '-' }}</td>
                        <td>
                            @php
                                $statusClasses = ['Draft'=>'secondary','Dispatched'=>'warning','Received'=>'info','Completed'=>'success','Cancelled'=>'danger'];
                            @endphp
                            <span class="badge text-bg-{{ $statusClasses[$m->manifest_status] ?? 'secondary' }}">{{ $m->manifest_status }}</span>
                        </td>
                        <td>{{ $m->vehicle_no ?? '-' }}</td>
                        <td>{{ $m->driver_name ?? '-' }}</td>
                        <td><small class="text-body-secondary" data-uts="{{ $m->created_at?->getTimestamp() }}">{{ $m->created_at?->format('d/m/Y H:i') }}</small></td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="/manifests/{{ $m->id }}" class="btn btn-outline-secondary" title="View"><i class="bi bi-eye"></i></a>
                                <a href="/print/manifests/{{ $m->id }}/sheet" target="_blank" class="btn btn-outline-success" title="Print"><i class="bi bi-printer"></i></a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-body-secondary">Tidak ada data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="mt-3">{{ $manifests->appends(request()->query())->links() }}</div>


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
    var startInput = document.querySelector('input[name="start_date"]');
    var endInput = document.querySelector('input[name="end_date"]');
    // Export buttons - pass current filter params
    var exportPdfBtn = document.getElementById('exportPdfBtn');
    var exportCsvBtn = document.getElementById('exportCsvBtn');
    if (exportPdfBtn && exportCsvBtn) {
        function updateExportLinks() {
            var qs = window.location.search;
            if (qs && qs.charAt(0) === '?') qs = qs.substring(1);
            exportCsvBtn.href = '/manifests/export/csv' + (qs ? '?' + qs : '');
        }
        updateExportLinks();
    }
    if (startInput && endInput) {
        startInput.addEventListener('change', function() {
            var start = new Date(startInput.value);
            var maxEnd = new Date(start);
            maxEnd.setDate(start.getDate() + 90);
            endInput.min = startInput.value;
            endInput.max = maxEnd.toISOString().split('T')[0];
            if (endInput.value && new Date(endInput.value) > maxEnd) {
                endInput.value = maxEnd.toISOString().split('T')[0];
            }
        });

        // Preset handlers
        document.querySelectorAll(".preset-opt").forEach(function(el) {
            el.addEventListener("click", function(e) {
                e.preventDefault();
                var days = el.getAttribute("data-days");
                var now = new Date();
                if (days === "this_month") {
                    var first = new Date(now.getFullYear(), now.getMonth(), 1);
                    startInput.value = first.toISOString().split("T")[0];
                } else if (days === "0") {
                    var d = new Date(now);
                    d.setDate(now.getDate() - 90);
                    startInput.value = d.toISOString().split("T")[0];
                } else {
                    var d = new Date(now);
                    d.setDate(now.getDate() - parseInt(days));
                    startInput.value = d.toISOString().split("T")[0];
                }
                endInput.value = now.toISOString().split("T")[0];
            });
        });
    }

    // PDF Preview modal
    var pdfPreviewModal = document.getElementById('pdfPreviewModal');
    if (pdfPreviewModal) {
        pdfPreviewModal.addEventListener('show.bs.modal', function() {
            var qs = window.location.search;
            if (qs && qs.charAt(0) === '?') qs = qs.substring(1);
            var previewUrl = '/manifests/export/pdf?' + qs + (qs ? '&' : '') + 'preview=1';
            var downloadUrl = '/manifests/export/pdf?' + qs;
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