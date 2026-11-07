@extends('layouts.app')
@section('title', 'Create Shipment')
@section('page_header')<h1 class="h2">Buat Resi Baru</h1>@endsection
@section('content')
<form action="/shipments" method="POST" data-bs-validate="" novalidate="" id="shipmentForm">
    @csrf
    <div class="row g-5">
        <div class="col-md-7 col-lg-8">
            <h4 class="mb-3">Data Pengirim</h4>
            <div class="row g-3">
                <div class="col-12">
                    <label for="customer_id" class="form-label">Customer (Perusahaan)</label>
                    <select name="customer_id" class="form-select" id="customer_id" style="max-height: 200px; overflow-y: auto;">
                        <option value="">-- Pilih Customer --</option>
                    </select>
                    <small class="text-muted">Ketik manual nama pengirim jika customer baru</small>
                </div>
                <div class="col-12">
                    <label for="sender_name" class="form-label">Nama Pengirim</label>
                    <input type="text" name="sender_name" class="form-control" id="sender_name" placeholder="Nama lengkap pengirim" required>
                    <div class="invalid-feedback">Nama pengirim wajib diisi.</div>
                </div>
                <div class="col-sm-6">
                    <label for="sender_phone" class="form-label">Telepon Pengirim</label>
                    <input type="text" name="sender_phone" class="form-control" inputmode="numeric" pattern="[0-9]*" oninput="this.value=this.value.replace(/[^0-9]/g,'')" id="sender_phone">
                </div>
            </div>

            <hr class="my-4">
            <h4 class="mb-3">Wilayah Pengirim</h4>
            <div class="row g-3">
                <div class="col-md-5">
                    <label for="sender_province" class="form-label">Provinsi</label>
                    <select name="sender_province_code" class="form-control" id="sender_province">
                        <option value="">Pilih...</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="sender_regency" class="form-label">Kab/Kota</label>
                    <select name="sender_regency_code" class="form-control" id="sender_regency">
                        <option value="">Pilih...</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="sender_district" class="form-label">Kecamatan</label>
                    <select name="sender_district_code" class="form-control" id="sender_district">
                        <option value="">Pilih...</option>
                    </select>
                </div>
                <div class="col-12">
                    <label for="sender_village" class="form-label">Kelurahan/Desa</label>
                    <select name="sender_village_code" class="form-control" id="sender_village">
                        <option value="">Pilih...</option>
                    </select>
                </div>
            </div>

            <div class="mt-3">
                <label for="sender_address" class="form-label">Alamat Pengirim</label>
                <textarea name="sender_address" class="form-control" id="sender_address" rows="2" placeholder="Alamat lengkap pengirim"></textarea>
            </div>

            <hr class="my-4">
            <h4 class="mb-3">Data Penerima</h4>
            <div class="row g-3">
                <div class="col-12">
                    <label for="receiver_name" class="form-label">Nama Penerima</label>
                    <input type="text" name="receiver_name" class="form-control" id="receiver_name" placeholder="Nama lengkap penerima" required>
                    <div class="invalid-feedback">Nama penerima wajib diisi.</div>
                </div>
                <div class="col-sm-6">
                    <label for="receiver_phone" class="form-label">Telepon Penerima</label>
                    <input type="text" name="receiver_phone" class="form-control" inputmode="numeric" pattern="[0-9]*" oninput="this.value=this.value.replace(/[^0-9]/g,'')" id="receiver_phone">
                </div>
            </div>

            <hr class="my-4">
            <h4 class="mb-3">Wilayah Penerima</h4>
            <div class="row g-3">
                <div class="col-md-5">
                    <label for="receiver_province" class="form-label">Provinsi</label>
                    <select name="receiver_province_code" class="form-control" id="receiver_province">
                        <option value="">Pilih...</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="receiver_regency" class="form-label">Kab/Kota</label>
                    <select name="receiver_regency_code" class="form-control" id="receiver_regency">
                        <option value="">Pilih...</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="receiver_district" class="form-label">Kecamatan</label>
                    <select name="receiver_district_code" class="form-control" id="receiver_district">
                        <option value="">Pilih...</option>
                    </select>
                </div>
                <div class="col-12">
                    <label for="receiver_village" class="form-label">Kelurahan/Desa</label>
                    <select name="receiver_village_code" class="form-control" id="receiver_village">
                        <option value="">Pilih...</option>
                    </select>
                </div>
            </div>

            <div class="mt-3">
                <label for="receiver_address" class="form-label">Alamat Penerima</label>
                <textarea name="receiver_address" class="form-control" id="receiver_address" rows="2" placeholder="Alamat lengkap penerima"></textarea>
            </div>

            <hr class="my-4">
            <h4 class="mb-3">Pengiriman</h4>
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
                    <label class="form-label">Jenis Layanan</label>
                    <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
                        <input type="radio" class="btn-check" name="service_type" id="service_reguler" value="REGULER" autocomplete="off" checked>
                        <label class="btn btn-outline-primary" for="service_reguler">REGULER</label>
                        <input type="radio" class="btn-check" name="service_type" id="service_express" value="EXPRESS" autocomplete="off">
                        <label class="btn btn-outline-primary" for="service_express">EXPRESS</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="po_number" class="form-label">Nomor PO</label>
                    <input type="text" name="po_number" class="form-control" id="po_number" placeholder="Nomor PO dari customer">
                </div>
                <div class="col-12">
                    <label class="form-label">Tipe Pembayaran</label>
                    <div class="btn-group" role="group" aria-label="Payment type">
                        <input type="radio" class="btn-check" name="payment_type" id="pay_kredit" value="kredit" autocomplete="off" checked>
                        <label class="btn btn-outline-secondary" for="pay_kredit">KREDIT</label>
                        <input type="radio" class="btn-check" name="payment_type" id="pay_lunas" value="lunas" autocomplete="off">
                        <label class="btn btn-outline-secondary" for="pay_lunas">LUNAS</label>
                        <input type="radio" class="btn-check" name="payment_type" id="pay_cod" value="cod" autocomplete="off">
                        <label class="btn btn-outline-secondary" for="pay_cod">COD</label>
                    </div>
                </div>
            </div>

            <hr class="my-4">
            <h4 class="mb-3">Detail Paket</h4>
            <div class="row g-3">
                <div class="col-sm-4">
                    <label for="total_koli" class="form-label">Total Koli</label>
                    <input type="number" name="total_koli" class="form-control" id="total_koli" min="1" value="1">
                </div>
                <div class="col-sm-4">
                    <label for="total_weight" class="form-label">Berat (kg)</label>
                    <input type="number" name="total_weight" class="form-control" id="total_weight" step="0.01" min="0.1" value="1">
                </div>
                <div class="col-sm-4">
                    <label for="package_description" class="form-label">Deskripsi Paket</label>
                    <input type="text" name="package_description" class="form-control" id="package_description" placeholder="Isi paket">
                </div>
            </div>
        </div>

        <div class="col-md-5 col-lg-4 order-md-last">
            <aside class="position-sticky" style="top: 1rem;">
                <div class="card">
                    <div class="card-header"><strong>Info Pengiriman</strong></div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between lh-sm">
                                <div><h6 class="my-0">Resi Number</h6><small class="text-body-secondary">Auto-generate setelah submit</small></div>
                                <span class="text-body-secondary">TRK-XXXX</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between lh-sm">
                                <div><h6 class="my-0">Status Awal</h6><small class="text-body-secondary">Created</small></div>
                                <span class="badge text-bg-info">Created</span>
                            </li>
                            <li class="list-group-item list-group-item-warning d-flex justify-content-between lh-sm">
                                <div><h6 class="my-0">Cabang Asal</h6></div>
                                <span class="fw-medium">{{ auth()->user()->branch?->name ?? '-' }}</span>
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
    <a href="/shipments" class="btn btn-outline-secondary">Batal</a>
    <button class="btn btn-primary" type="submit" form="shipmentForm"><i class="bi bi-save me-2"></i>Buat Resi</button>
</div>

<script>
function loadOptions(url, selectId) {
    fetch(url)
        .then(r => r.json())
        .then(data => {
            const sel = document.getElementById(selectId);
            if (!sel) return;
            sel.innerHTML = '<option value="">-- Pilih --</option>';
            data.forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.code;
                opt.textContent = item.name;
                sel.appendChild(opt);
            });
        })
        .catch(() => {});
}
function setupCascading(prefix) {
    const prov = document.getElementById(prefix + '_province');
    const reg = document.getElementById(prefix + '_regency');
    const dist = document.getElementById(prefix + '_district');
    const vill = document.getElementById(prefix + '_village');
    if (prov) prov.addEventListener('change', function() {
        loadOptions('/api/regions/regencies?province=' + this.value, prefix + '_regency');
        if (dist) dist.innerHTML = '<option value="">-- Pilih --</option>';
        if (vill) vill.innerHTML = '<option value="">-- Pilih --</option>';
    });
    if (reg) reg.addEventListener('change', function() {
        loadOptions('/api/regions/districts?regency=' + this.value, prefix + '_district');
        if (vill) vill.innerHTML = '<option value="">-- Pilih --</option>';
    });
    if (dist) dist.addEventListener('change', function() {
        loadOptions('/api/regions/villages?district=' + this.value, prefix + '_village');
    });
}
document.addEventListener('DOMContentLoaded', function() {
    loadOptions('/api/regions/provinces', 'sender_province');
    loadOptions('/api/regions/provinces', 'receiver_province');
    setupCascading('sender');
    setupCascading('receiver');

    // Load customers into dropdown
    function loadCustomers() {
        fetch('/api/customers/search?q=')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                var sel = document.getElementById('customer_id');
                sel.innerHTML = '<option value="">-- Pilih Customer --</option>';
                data.forEach(function(c) {
                    var opt = document.createElement('option');
                    opt.value = c.id;
                    opt.textContent = (c.company_name || c.name) + (c.phone ? ' - ' + c.phone : '');
                    opt.dataset.name = c.name;
                    opt.dataset.phone = c.phone || '';
                    opt.dataset.address = c.address || '';
                    opt.dataset.province = c.province_code || '';
                    opt.dataset.regency = c.regency_code || '';
                    opt.dataset.district = c.district_code || '';
                    opt.dataset.village = c.village_code || '';
                    sel.appendChild(opt);
                });
            });
    }
    loadCustomers();

    // Auto-fill sender fields when customer selected
    document.getElementById('customer_id').addEventListener('change', function() {
        var opt = this.options[this.selectedIndex];
        if (!opt.value) return;
        document.getElementById('sender_name').value = opt.dataset.name || '';
        document.getElementById('sender_phone').value = opt.dataset.phone || '';
        document.getElementById('sender_address').value = opt.dataset.address || '';
        if (opt.dataset.province) {
            var provSel = document.getElementById('sender_province');
            provSel.value = opt.dataset.province;
            fetch('/api/regions/regencies?province=' + opt.dataset.province)
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    var regSel = document.getElementById('sender_regency');
                    regSel.innerHTML = '<option value="">-- Pilih --</option>';
                    data.forEach(function(item) {
                        var o = document.createElement('option');
                        o.value = item.code;
                        o.textContent = item.name;
                        regSel.appendChild(o);
                    });
                    if (opt.dataset.regency) regSel.value = opt.dataset.regency;
                    if (opt.dataset.regency) {
                        fetch('/api/regions/districts?regency=' + opt.dataset.regency)
                            .then(function(r) { return r.json(); })
                            .then(function(data2) {
                                var distSel = document.getElementById('sender_district');
                                distSel.innerHTML = '<option value="">-- Pilih --</option>';
                                data2.forEach(function(item) {
                                    var o = document.createElement('option');
                                    o.value = item.code;
                                    o.textContent = item.name;
                                    distSel.appendChild(o);
                                });
                                if (opt.dataset.district) distSel.value = opt.dataset.district;
                                if (opt.dataset.district) {
                                    fetch('/api/regions/villages?district=' + opt.dataset.district)
                                        .then(function(r) { return r.json(); })
                                        .then(function(data3) {
                                            var villSel = document.getElementById('sender_village');
                                            villSel.innerHTML = '<option value="">-- Pilih --</option>';
                                            data3.forEach(function(item) {
                                                var o = document.createElement('option');
                                                o.value = item.code;
                                                o.textContent = item.name;
                                                villSel.appendChild(o);
                                            });
                                            if (opt.dataset.village) villSel.value = opt.dataset.village;
                                        });
                                }
                            });
                    }
                });
        }
    });
});

// PO number duplicate check
var poInput = document.getElementById('po_number');
if (poInput) {
    poInput.addEventListener('blur', function() {
        var poVal = this.value.trim();
        if (!poVal) return;
        var custSelect = document.querySelector('select[name="customer_id"]');
        var custId = custSelect ? custSelect.value : '';
        fetch('/api/check-po?po_number=' + encodeURIComponent(poVal) + '&customer_id=' + encodeURIComponent(custId))
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.exists) {
                    var modal = new bootstrap.Modal(document.getElementById('poWarningModal'));
                    document.getElementById('poWarningText').textContent = 'Nomor PO "' + poVal + '" sudah digunakan oleh customer "' + data.customer_name + '" (resi: ' + data.resi_no + ').';
                    modal.show();
                }
            });
    });
}
</script>

<!-- PO Warning Modal -->
<div class="modal fade" id="poWarningModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">Peringatan: PO Number Duplikat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="poWarningText"></p>
                <p class="text-muted small">Anda tetap bisa melanjutkan, tapi pastikan PO number ini memang benar untuk customer ini.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>
@endsection