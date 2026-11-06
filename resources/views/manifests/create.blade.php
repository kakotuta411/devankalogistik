@extends('layouts.app')
@section('title', 'Create Manifest')
@section('page_header')<h1 class="h2">Buat Manifest</h1>@endsection
@section('content')
<form action="/manifests" method="POST" data-bs-validate="" novalidate="" id="manifestForm">
    @csrf
    <div class="row g-5">
        <div class="col-md-7 col-lg-8">
            <h4 class="mb-3">Data Manifest</h4>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="destination_branch_id" class="form-label">Cabang Tujuan *</label>
                    <select name="destination_branch_id" class="form-control" id="destination_branch_id" required>
                        <option value="">Pilih cabang tujuan...</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->branch_code }} — {{ $b->name }} ({{ $b->city }})</option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback">Cabang tujuan wajib dipilih.</div>
                </div>
                <div class="col-md-6">
                    <label for="vehicle_no" class="form-label">No Kendaraan</label>
                    <input type="text" name="vehicle_no" class="form-control" id="vehicle_no" placeholder="B 1234 CD">
                </div>
                <div class="col-sm-6">
                    <label for="driver_name" class="form-label">Nama Driver</label>
                    <input type="text" name="driver_name" class="form-control" id="driver_name" placeholder="Nama supir">
                </div>
                <div class="col-sm-6">
                </div>
            </div>

            <hr class="my-4">
            <h4 class="mb-3">Assign Shipments</h4>
            <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                @forelse($availableShipments as $branchId => $shipments)
                @php($destBranch = \App\Models\Branch::find($branchId))
                <div class="mb-3">
                    <h6 class="text-primary">{{ $destBranch?->branch_code }} — {{ $destBranch?->name }} ({{ $destBranch?->city }})</h6>
                    @foreach($shipments as $s)
                    <div class="form-field mb-1 ms-3">
                        <input type="checkbox" name="shipment_ids[]" value="{{ $s->id }}" class="check dest-{{ $branchId }}" id="ship_{{ $s->id }}">
                        <label for="ship_{{ $s->id }}">{{ $s->resi_no }} — {{ $s->sender_name }} → {{ $s->receiver_name }}</label>
                    </div>
                    @endforeach
                </div>
                @empty
                <p class="text-body-secondary small">Tidak ada shipment yang tersedia</p>
                @endforelse
            </div>
        </div>

        <div class="col-md-5 col-lg-4 order-md-last">
            <aside class="position-sticky" style="top: 1rem;">
                <div class="card">
                    <div class="card-header"><strong>Info Manifest</strong></div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between lh-sm">
                                <div><h6 class="my-0">Manifest No</h6><small class="text-body-secondary">Auto-generate setelah submit</small></div>
                                <span class="text-body-secondary">MNF-XXXX</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between lh-sm">
                                <div><h6 class="my-0">Status Awal</h6><small class="text-body-secondary">Draft</small></div>
                                <span class="badge text-bg-secondary">Draft</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</form>

<hr class="my-4">
<div class="d-flex justify-content-end gap-2">
    <a href="/manifests" class="btn btn-outline-secondary">Batal</a>
    <button class="btn btn-primary" type="submit" form="manifestForm"><i class="bi bi-save me-2"></i>Buat Manifest</button>
</div>
@endsection

@push('scripts')
<script>
// Filter shipment checkboxes by destination branch
document.getElementById('destination_branch_id').addEventListener('change', function() {
    var destId = this.value;
    var checkboxes = document.querySelectorAll('.check');
    var groups = document.querySelectorAll('.mb-3');
    var hasVisible = false;
    groups.forEach(function(g) {
        var groupDest = g.querySelector('.check')?.classList?.toString().match(/dest-([a-f0-9-]+)/)?.[1];
        if (destId === '' || groupDest === destId) {
            g.style.display = '';
            hasVisible = true;
        } else {
            g.style.display = 'none';
            g.querySelectorAll('.check').forEach(function(cb) { cb.checked = false; });
        }
    });
    // Show message if no shipments for selected destination
    var emptyMsg = document.getElementById('no-shipments-msg');
    if (!emptyMsg) {
        emptyMsg = document.createElement('p');
        emptyMsg.id = 'no-shipments-msg';
        emptyMsg.className = 'text-body-secondary small';
        emptyMsg.style.display = 'none';
        emptyMsg.textContent = 'Tidak ada shipment yang tersedia untuk cabang tujuan ini';
        document.querySelector('.border.rounded.p-3').appendChild(emptyMsg);
    }
    emptyMsg.style.display = (destId !== '' && !hasVisible) ? '' : 'none';
});
</script>
@endpush
