@extends('layouts.app')
@section('title', 'Manifest Detail')
@section('page_header')<h1 class="h2">Manifest: {{ $manifest->manifest_no }}</h1>@endsection
@section('content')
@php $m = $manifest; @endphp
<div class="d-flex justify-content-between align-items-center mb-4">
    <a href="/print/manifests/{{ $m->id }}/sheet" target="_blank" class="btn btn-success"><i class="bi bi-printer me-2"></i>Print Sheet</a>
    @if($m->manifest_status === 'Draft')
    @if(auth()->user()->canManageManifests())
        <button type="button" class="btn btn-outline-warning edit-btn" data-edit-url="/manifests/{{ $m->id }}/edit" data-edit-title="Edit Manifest"><i class="bi bi-pencil me-2"></i>Edit</button>
        @endif
    @endif
</div>
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><strong>Info Manifest</strong></div>
            <div class="card-body">
                <p class="mb-1"><strong>Status:</strong> <span class="badge {{ $m->manifest_status === 'Completed' || $m->manifest_status === 'Cancelled' ? 'text-bg-success' : ($m->manifest_status === 'Received' ? 'text-bg-info' : ($m->manifest_status === 'Dispatched' ? 'text-bg-warning' : 'text-bg-secondary')) }}">{{ $m->manifest_status }}</span></p>
                <p class="mb-1"><strong>Asal:</strong> {{ $m->originBranch?->name }} ({{ $m->originBranch?->city }})</p>
                <p class="mb-1"><strong>Tujuan:</strong> {{ $m->destinationBranch?->name }} ({{ $m->destinationBranch?->city }})</p>
                <p class="mb-1"><strong>Kendaraan:</strong> {{ $m->vehicle_no ?? '-' }}</p>
                <p class="mb-1"><strong>Driver:</strong> {{ $m->driver_name ?? '-' }}</p>
                <p class="mb-1"><strong>Dispatch:</strong> <span data-uts="{{ $m->dispatch_time?->getTimestamp() }}" class="text-body-secondary">{{ $m->dispatch_time?->format('d/m/Y H:i') ?? '-' }}</span></p>
                <p class="mb-0"><strong>Received:</strong> <span data-uts="{{ $m->receive_time?->getTimestamp() }}" class="text-body-secondary">{{ $m->receive_time?->format('d/m/Y H:i') ?? '-' }}</span></p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><strong>Daftar Muatan</strong></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr><th>No</th><th>Resi</th><th>Tujuan</th><th>Koli</th></tr>
                    </thead>
                    <tbody>
                        @foreach($m->items as $i => $item)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td><a href="/shipments/{{ $item->shipment?->id }}" class="text-decoration-none">{{ $item->shipment?->resi_no }}</a></td>
                            <td>{{ $item->shipment?->destinationBranch?->city }}</td>
                            <td>{{ $item->shipment?->total_koli }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="d-flex gap-2">
    @if(auth()->user()->canManageManifests())
    @if($m->manifest_status === 'Draft')
        <form action="/manifests/{{ $m->id }}/dispatch" method="POST">
            @csrf
            <button type="submit" class="btn btn-warning">Dispatch</button>
        </form>
    @endif
    @if($m->manifest_status === 'Dispatched')
        <form action="/manifests/{{ $m->id }}/receive" method="POST">
            @csrf
            <button type="submit" class="btn btn-success">Terima & Bongkar</button>
        </form>
    @endif
    @if($m->manifest_status === 'Received')
        <form action="/manifests/{{ $m->id }}/complete" method="POST">
            @csrf
            <button type="submit" class="btn btn-success">Selesaikan</button>
        </form>
    @endif
    @endif
<!-- Edit Modal -->
<style>
#editModal .modal-dialog { width: 95vw; max-width: 95vw; height: 95vh; }
#editModal .modal-content { height: 100%; }
#editModal .modal-body { overflow-y: auto; }
</style>
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="editFormBody">
                <div class="text-center p-4">Loading...</div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var editModalEl = document.getElementById('editModal');
    var editModal = null;

    document.querySelectorAll('.edit-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var url = this.getAttribute('data-edit-url');
            var title = this.getAttribute('data-edit-title') || 'Edit';
            if (!editModal) editModal = new bootstrap.Modal(editModalEl);
            document.querySelector('#editModal .modal-title').textContent = title;
            document.getElementById('editFormBody').innerHTML = '<div class="text-center p-4">Loading...</div>';
            editModal.show();

            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function(r) { return r.text(); })
                .then(function(html) {
                    var parser = new DOMParser();
                    var doc = parser.parseFromString(html, 'text/html');
                    var form = doc.querySelector('form input[name="_method"][value="PUT"]') ? doc.querySelector('form input[name="_method"][value="PUT"]').closest('form') : doc.querySelector('form');
                    if (form) {
                        var cancel = form.querySelector('.btn-outline-secondary[href]');
                        if (cancel) cancel.remove();
                        document.getElementById('editFormBody').innerHTML = '';
                        document.getElementById('editFormBody').appendChild(form);

                        form.addEventListener('submit', function(e) {
                            e.preventDefault();
                            var fd = new FormData(form);
                            fetch(form.action, {
                                method: 'POST', body: fd,
                                headers: { 'X-Requested-With': 'XMLHttpRequest' }
                            }).then(function(resp) {
                                if (resp.ok || resp.redirected) {
                                    editModal.hide();
                                } else {
                                    return resp.text();
                                }
                            }).then(function(html) {
                                if (html) {
                                    document.getElementById('editFormBody').innerHTML = '<div class="alert alert-danger">' + html.substring(0, 300) + '</div>';
                                }
                            }).catch(function() {
                                editModal.hide();
                            });
                        });
                    }
                });
        });
    });

    editModalEl.addEventListener('hidden.bs.modal', function() {
        document.getElementById('editFormBody').innerHTML = '';
        window.location.reload();
    });
});
</script>
@endsection
