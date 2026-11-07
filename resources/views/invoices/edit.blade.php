@extends('layouts.app')
@section('title', 'Edit Invoice')
@section('page_header')<h1 class="h2">Edit Invoice: {{ $invoice->invoice_no }}</h1>@endsection
@section('content')
<form action="/invoices/{{ $invoice->id }}" method="POST" class="card">
    @csrf @method('PUT')
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Nama Customer</label>
                <input type="text" name="customer_name" value="{{ $invoice->customer_name }}" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">Telepon</label>
                <input type="text" name="customer_phone" value="{{ $invoice->customer_phone }}" class="form-control" inputmode="numeric" pattern="[0-9]*" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
            </div>
        </div>
        <div class="mt-3">
            <label class="form-label">Alamat</label>
            <textarea name="customer_address" class="form-control" rows="2">{{ $invoice->customer_address }}</textarea>
        </div>
        <div class="row g-3 mt-1">
            <div class="col-md-3">
                <label for="customer_province" class="form-label small">Provinsi</label>
                <select name="customer_province_code" class="form-select form-select-sm" id="customer_province" data-selected="{{ $invoice->customer_province_code }}">
                    <option value="">Pilih...</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="customer_regency" class="form-label small">Kab/Kota</label>
                <select name="customer_regency_code" class="form-select form-select-sm" id="customer_regency" data-selected="{{ $invoice->customer_regency_code }}">
                    <option value="">Pilih...</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="customer_district" class="form-label small">Kecamatan</label>
                <select name="customer_district_code" class="form-select form-select-sm" id="customer_district" data-selected="{{ $invoice->customer_district_code }}">
                    <option value="">Pilih...</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="customer_village" class="form-label small">Kelurahan/Desa</label>
                <select name="customer_village_code" class="form-select form-select-sm" id="customer_village" data-selected="{{ $invoice->customer_village_code }}">
                    <option value="">Pilih...</option>
                </select>
            </div>
        </div>
        <div class="row g-3 mt-1">
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="customer_email" value="{{ $invoice->customer_email }}" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">Status</label>
                <select name="invoice_status" class="form-select">
                    <option value="Draft" {{ $invoice->invoice_status === 'Draft' ? 'selected' : '' }}>Draft</option>
                    <option value="Paid" {{ $invoice->invoice_status === 'Paid' ? 'selected' : '' }}>Paid</option>
                    <option value="Cancelled" {{ $invoice->invoice_status === 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
        </div>
        <div class="row g-3 mt-1">
            <div class="col-md-3">
                <label class="form-label">Biaya Pengiriman (per kg)</label>
                <input type="number" name="shipping_cost" value="{{ $invoice->shipping_cost }}" class="form-control" step="0.01">
            </div>
            <div class="col-md-3">
                <label class="form-label">Biaya Packaging</label>
                <input type="number" name="packaging_cost" value="{{ $invoice->packaging_cost }}" class="form-control" step="1">
            </div>
            <div class="col-md-3">
                <label class="form-label">Charge Alat</label>
                <input type="number" name="equipment_charge" value="{{ $invoice->equipment_charge }}" class="form-control" step="1">
            </div>
            <div class="col-md-3">
                <label class="form-label">PPFTZ 03</label>
                <input type="number" name="ppftz03" value="{{ $invoice->ppftz03 }}" class="form-control" step="1">
            </div>
        </div>
        <div class="row g-3 mt-1">
            <div class="col-md-3">
                <label class="form-label">PPN (%)</label>
                <input type="number" name="ppn" value="{{ $invoice->ppn }}" class="form-control" step="0.01">
            </div>
            <div class="col-md-3">
                <label class="form-label">PPH (%)</label>
                <input type="number" name="pph" value="{{ $invoice->pph }}" class="form-control" step="0.01">
            </div>
            <div class="col-md-3">
                <label class="form-label">Diskon (%)</label>
                <input type="number" name="discount_value" value="{{ $invoice->discount_value }}" class="form-control" step="0.01">
            </div>
            <div class="col-md-3">
                <label class="form-label">Biaya Lain-lain</label>
                <input type="number" name="other_costs" value="{{ $invoice->other_costs }}" class="form-control" step="1">
            </div>
        </div>
        <div class="row g-3 mt-1">
            <div class="col-md-3">
                <label class="form-label">Tanggal</label>
                <input type="date" name="invoice_date" value="{{ $invoice->invoice_date?->format('Y-m-d') }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Due Date</label>
                <input type="date" name="due_date" value="{{ $invoice->due_date?->format('Y-m-d') ?? '' }}" class="form-control">
            </div>
        </div>
        <div class="mt-3">
            <label class="form-label">Catatan</label>
            <textarea name="notes" class="form-control" rows="2">{{ $invoice->notes }}</textarea>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-end gap-2">
        <a href="/invoices" class="btn btn-outline-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">Update</button>
    </div>
</form>

<script>
function loadOptions(url, selectId) {
    fetch(url).then(r => r.json()).then(data => {
        var sel = document.getElementById(selectId);
        if (!sel) return;
        sel.innerHTML = '<option value="">-- Pilih --</option>';
        data.forEach(function(item) {
            var opt = document.createElement('option');
            opt.value = item.code;
            opt.textContent = item.name;
            sel.appendChild(opt);
        });
    }).catch(function() {});
}
function setupCascading(prefix) {
    var prov = document.getElementById(prefix + '_province');
    var reg = document.getElementById(prefix + '_regency');
    var dist = document.getElementById(prefix + '_district');
    var vill = document.getElementById(prefix + '_village');
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
    loadOptions('/api/regions/provinces', 'customer_province');
    setupCascading('customer');
});
</script>
@endsection