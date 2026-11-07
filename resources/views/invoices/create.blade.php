@extends('layouts.app')
@section('title', 'Create Invoice')
@section('page_header')<h1 class="h2">Buat Invoice Baru</h1>@endsection
@section('content')
<form action="/invoices" method="POST" data-bs-validate="" novalidate="" id="invoiceForm">
    @csrf
    <input type="hidden" name="po_id" value="{{ $po->id ?? '' }}">
    <div class="row g-5">
        <div class="col-md-7 col-lg-8">
            <h4 class="mb-3">Data Customer</h4>
            <div class="row g-3">
                <div class="col-12">
                    <label for="customer_id" class="form-label">Customer (Perusahaan)</label>
                    <select name="customer_id" class="form-select" id="customer_id" style="max-height: 200px; overflow-y: auto;">
                        <option value="">-- Pilih Customer --</option>
                    </select>
                </div>
                <div class="col-sm-6">
                    <label for="customer_name" class="form-label">Nama Customer</label>
                    <input type="text" name="customer_name" class="form-control" id="customer_name" placeholder="Nama customer / perusahaan">
                </div>
                <div class="col-12">
                    <label class="form-label">Pilih Resi (berdasarkan PO)</label>
                    <div id="po_groups_container" class="border rounded p-3" style="max-height:400px; overflow-y:auto;">
                        <p class="text-muted">Pilih customer terlebih dahulu untuk menampilkan daftar PO dan resi.</p>
                    </div>
                </div>
                <div class="col-sm-6">
                    <label for="customer_phone" class="form-label">Telepon</label>
                    <input type="text" name="customer_phone" class="form-control" id="customer_phone" placeholder="08xxxxxxxxxx" inputmode="numeric" pattern="[0-9]*" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                </div>
                <div class="col-sm-6">
                    <label for="customer_email" class="form-label">Email <span class="text-body-secondary">(Optional)</span></label>
                    <input type="email" name="customer_email" class="form-control" id="customer_email" placeholder="email@example.com">
                </div>
                <div class="col-sm-6">
                    <label class="form-label">Branch</label>
                    <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id }}">
                    <input type="text" class="form-control" value="{{ auth()->user()->branch?->branch_code }} — {{ auth()->user()->branch?->name }}" readonly>
                </div>
                <div class="col-12">
                    <h6 class="mb-2">Wilayah Customer</h6>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="customer_province" class="form-label small">Provinsi</label>
                            <select name="customer_province_code" class="form-select form-select-sm" id="customer_province">
                                <option value="">Pilih...</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="customer_regency" class="form-label small">Kab/Kota</label>
                            <select name="customer_regency_code" class="form-select form-select-sm" id="customer_regency">
                                <option value="">Pilih...</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="customer_district" class="form-label small">Kecamatan</label>
                            <select name="customer_district_code" class="form-select form-select-sm" id="customer_district">
                                <option value="">Pilih...</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="customer_village" class="form-label small">Kelurahan/Desa</label>
                            <select name="customer_village_code" class="form-select form-select-sm" id="customer_village">
                                <option value="">Pilih...</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <label for="customer_address" class="form-label">Alamat</label>
                    <textarea name="customer_address" class="form-control" id="customer_address" rows="2" placeholder="Alamat customer"></textarea>
                </div>
            </div>

            <hr class="my-4">
            <h4 class="mb-3">Detail Invoice</h4>
            <div class="row g-3">
                <div class="col-sm-6">
                    <label for="invoice_date" class="form-label">Tanggal Invoice</label>
                    <input type="date" name="invoice_date" class="form-control" id="invoice_date">
                </div>
                <div class="col-sm-6">
                    <label for="due_date" class="form-label">Due Date</label>
                    <input type="date" name="due_date" class="form-control" id="due_date">
                </div>
                <div class="col-sm-4">
                    <label for="shipping_cost" class="form-label">Biaya Pengiriman (per kg)</label>
                    <input type="number" name="shipping_cost" class="form-control" id="shipping_cost" step="0.01" value="0">
                </div>
                <div class="col-sm-4">
                    <label for="packaging_cost" class="form-label">Biaya Packaging</label>
                    <input type="number" name="packaging_cost" class="form-control" id="packaging_cost" step="1" value="0">
                </div>
                <div class="col-sm-4">
                    <label for="equipment_charge" class="form-label">Charge Alat</label>
                    <input type="number" name="equipment_charge" class="form-control" id="equipment_charge" step="1" value="0">
                </div>
                <div class="col-sm-4">
                    <label for="ppftz03" class="form-label">PPFTZ 03</label>
                    <input type="number" name="ppftz03" class="form-control" id="ppftz03" step="1" value="0">
                </div>
                <div class="col-sm-4">
                    <label for="ppn" class="form-label">PPN (%)</label>
                    <input type="number" name="ppn" class="form-control" id="ppn" step="0.01" value="0">
                </div>
                <div class="col-sm-4">
                    <label for="pph" class="form-label">PPH (%)</label>
                    <input type="number" name="pph" class="form-control" id="pph" step="0.01" value="0">
                </div>
                <div class="col-sm-4">
                    <label for="discount_value" class="form-label">Diskon (%)</label>
                    <input type="number" name="discount_value" class="form-control" id="discount_value" step="0.01" value="0">
                </div>
                <div class="col-sm-4">
                    <label for="other_costs" class="form-label">Biaya Lain-lain</label>
                    <input type="number" name="other_costs" class="form-control" id="other_costs" step="1" value="0">
                </div>
                <div class="col-12">
                    <label for="notes" class="form-label">Catatan</label>
                    <textarea name="notes" class="form-control" id="notes" rows="2" placeholder="Catatan invoice..."></textarea>
                </div>
            </div>

        </div>
        <div class="col-md-5 col-lg-4 order-md-last">
            <aside class="position-sticky" style="top: 1rem;">
                <div class="card">
                    <div class="card-header"><strong>Info Invoice</strong></div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between lh-sm">
                                <div><h6 class="my-0">Invoice No</h6><small class="text-body-secondary">Auto-generate setelah submit</small></div>
                                <span class="text-body-secondary">INV-XXXX</span>
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
<script>
function loadOptions(url, selectId) {
    fetch(url)
        .then(r => r.json())
        .then(data => {
            const sel = document.getElementById(selectId);
            if (!sel) return;
            sel.innerHTML = '<option value="">Pilih...</option>';
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
        if (dist) dist.innerHTML = '<option value="">Pilih...</option>';
        if (vill) vill.innerHTML = '<option value="">Pilih...</option>';
    });
    if (reg) reg.addEventListener('change', function() {
        loadOptions('/api/regions/districts?regency=' + this.value, prefix + '_district');
        if (vill) vill.innerHTML = '<option value="">Pilih...</option>';
    });
    if (dist) dist.addEventListener('change', function() {
        loadOptions('/api/regions/villages?district=' + this.value, prefix + '_village');
    });
}
document.addEventListener('DOMContentLoaded', function() {
    loadOptions('/api/regions/provinces', 'customer_province');
    setupCascading('customer');

    // Load customers into dropdown
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
                opt.dataset.email = c.email || '';
                opt.dataset.province = c.province_code || '';
                opt.dataset.regency = c.regency_code || '';
                opt.dataset.district = c.district_code || '';
                opt.dataset.village = c.village_code || '';
                sel.appendChild(opt);
            });
        });
        var poId = '{{ $po->id ?? '' }}';
        var poCustomerId = '{{ $po->customer_id ?? '' }}';
        if (poId && poCustomerId) {
            // Auto-select customer after dropdown loaded
            setTimeout(function() {
                var sel = document.getElementById('customer_id');
                sel.value = poCustomerId;
                sel.dispatchEvent(new Event('change'));
                // After PO groups load, check all shipments from this PO
                var checkInterval = setInterval(function() {
                    var checkboxes = document.querySelectorAll('input[name="shipment_ids[]"]');
                    if (checkboxes.length > 0) {
                        clearInterval(checkInterval);
                        checkboxes.forEach(function(cb) { cb.checked = true; });
                    }
                }, 500);
                setTimeout(function() { clearInterval(checkInterval); }, 10000);
            }, 500);
        }
        document.getElementById('customer_id').addEventListener('change', function() {
        var opt = this.options[this.selectedIndex];
        if (!opt.value) return;
        document.getElementById('customer_name').value = opt.dataset.name || '';
        document.getElementById('customer_phone').value = opt.dataset.phone || '';
        document.getElementById('customer_address').value = opt.dataset.address || '';
        document.getElementById('customer_email').value = opt.dataset.email || '';
        if (opt.dataset.province) {
            var provSel = document.getElementById('customer_province');
            provSel.value = opt.dataset.province;
            fetch('/api/regions/regencies?province=' + opt.dataset.province)
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    var regSel = document.getElementById('customer_regency');
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
                                var distSel = document.getElementById('customer_district');
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
                                            var villSel = document.getElementById('customer_village');
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
        loadPOGroups(opt.value);
    });

    function loadPOGroups(customerId) {
        var container = document.getElementById('po_groups_container');
        container.innerHTML = '<p class="text-muted">Memuat...</p>';
        fetch('/api/customers/' + customerId + '/shipments')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                container.innerHTML = '';
                if (data.length === 0) {
                    container.innerHTML = '<p class="text-muted">Tidak ada resi untuk customer ini.</p>';
                    return;
                }
                data.forEach(function(group) {
                    var div = document.createElement('div');
                    div.className = 'mb-3';
                    var header = document.createElement('div');
                    header.className = 'fw-bold mb-1';
                    header.textContent = 'PO: ' + group.po_number;
                    div.appendChild(header);
                    group.shipments.forEach(function(sh) {
                        var check = document.createElement('div');
                        check.className = 'form-check';
                        var cb = document.createElement('input');
                        cb.type = 'checkbox';
                        cb.className = 'form-check-input';
                        cb.name = 'shipment_ids[]';
                        cb.value = sh.id;
                        cb.id = 'ship_' + sh.id;
                        var label = document.createElement('label');
                        label.className = 'form-check-label';
                        label.htmlFor = 'ship_' + sh.id;
                        label.textContent = sh.resi_no + ' — ' + sh.receiver_name + ' (' + sh.total_koli + ' koli, ' + sh.total_weight + ' kg) — ' + sh.shipment_status;
                        check.appendChild(cb);
                        check.appendChild(label);
                        div.appendChild(check);
                    });
                    container.appendChild(div);
                });
            });
    }
});
</script>
</form>

<hr class="my-4">
<div class="d-flex justify-content-end gap-2">
    <a href="/invoices" class="btn btn-outline-secondary">Batal</a>
    <button class="btn btn-primary" type="submit" form="invoiceForm"><i class="bi bi-save me-2"></i>Buat Invoice</button>
</div>
@endsection
