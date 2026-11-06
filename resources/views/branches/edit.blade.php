@extends('layouts.app')
@section('title', 'Edit Branch')
@section('page_header')<h1 class="h2">Edit Cabang</h1>@endsection
@section('content')
<form action="/branches/{{ $branch->id }}" method="POST" class="card">
    @csrf @method('PUT')
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Kode Cabang *</label>
                <input type="text" name="branch_code" value="{{ $branch->branch_code }}" class="form-control" required>
            </div>
            <div class="col-md-8">
                <label class="form-label">Nama Cabang *</label>
                <input type="text" name="name" value="{{ $branch->name }}" class="form-control" required>
            </div>
        </div>
        <div class="row g-3 mt-1">
            <div class="col-md-6">
                <label class="form-label">Provinsi</label>
                <select id="province_code" name="province_code" class="form-select" data-selected="{{ $branch->province_code }}">
                    <option value="">-- Pilih Provinsi --</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Kab/Kota</label>
                <select id="regency_code" name="regency_code" class="form-select" data-selected="{{ $branch->regency_code }}">
                    <option value="">-- Pilih Kab/Kota --</option>
                </select>
            </div>
        </div>
        <div class="row g-3 mt-1">
            <div class="col-md-6">
                <label class="form-label">Kecamatan</label>
                <select id="district_code" name="district_code" class="form-select" data-selected="{{ $branch->district_code }}">
                    <option value="">-- Pilih Kecamatan --</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Kelurahan/Desa</label>
                <select id="village_code" name="village_code" class="form-select" data-selected="{{ $branch->village_code }}">
                    <option value="">-- Pilih Kelurahan --</option>
                </select>
            </div>
        </div>
        <div class="mt-3">
            <label class="form-label">Alamat</label>
            <textarea name="address" class="form-control" rows="2">{{ $branch->address }}</textarea>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-end gap-2">
        <a href="/branches" class="btn btn-outline-secondary">Batal</a>
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
document.addEventListener('DOMContentLoaded', function() {
    var provSel = document.getElementById('province_code');
    var regSel = document.getElementById('regency_code');
    var distSel = document.getElementById('district_code');
    var villSel = document.getElementById('village_code');
    var selectedProv = provSel.getAttribute('data-selected');
    var selectedReg = regSel.getAttribute('data-selected');
    var selectedDist = distSel.getAttribute('data-selected');
    var selectedVill = villSel.getAttribute('data-selected');

    loadOptions('/api/regions/provinces', 'province_code');

    if (selectedProv) {
        setTimeout(function() {
            provSel.value = selectedProv;
            loadOptions('/api/regions/regencies?province=' + selectedProv, 'regency_code');
            if (selectedReg) {
                setTimeout(function() {
                    regSel.value = selectedReg;
                    loadOptions('/api/regions/districts?regency=' + selectedReg, 'district_code');
                    if (selectedDist) {
                        setTimeout(function() {
                            distSel.value = selectedDist;
                            loadOptions('/api/regions/villages?district=' + selectedDist, 'village_code');
                            if (selectedVill) {
                                setTimeout(function() {
                                    villSel.value = selectedVill;
                                }, 500);
                            }
                        }, 500);
                    }
                }, 500);
            }
        }, 500);
    }

    provSel.addEventListener('change', function() {
        loadOptions('/api/regions/regencies?province=' + this.value, 'regency_code');
        distSel.innerHTML = '<option value="">-- Pilih Kecamatan --</option>';
        villSel.innerHTML = '<option value="">-- Pilih Kelurahan --</option>';
    });
    regSel.addEventListener('change', function() {
        loadOptions('/api/regions/districts?regency=' + this.value, 'district_code');
        villSel.innerHTML = '<option value="">-- Pilih Kelurahan --</option>';
    });
    distSel.addEventListener('change', function() {
        loadOptions('/api/regions/villages?district=' + this.value, 'village_code');
    });
});
</script>
@endsection