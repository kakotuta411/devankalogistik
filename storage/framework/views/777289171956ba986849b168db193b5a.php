<?php $__env->startSection('title', 'Create Branch'); ?>
<?php $__env->startSection('page_header'); ?><h1 class="h2">Tambah Cabang</h1><?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<form action="/branches" method="POST" class="card">
    <?php echo csrf_field(); ?>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Kode Cabang *</label>
                <input type="text" name="branch_code" class="form-control" required>
            </div>
            <div class="col-md-8">
                <label class="form-label">Nama Cabang *</label>
                <input type="text" name="name" class="form-control" required>
            </div>
        </div>
        <div class="row g-3 mt-1">
            <div class="col-md-6">
                <label class="form-label">Provinsi</label>
                <select id="province_code" name="province_code" class="form-select">
                    <option value="">-- Pilih Provinsi --</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Kab/Kota</label>
                <select id="regency_code" name="regency_code" class="form-select">
                    <option value="">-- Pilih Kab/Kota --</option>
                </select>
            </div>
        </div>
        <div class="row g-3 mt-1">
            <div class="col-md-6">
                <label class="form-label">Kecamatan</label>
                <select id="district_code" name="district_code" class="form-select">
                    <option value="">-- Pilih Kecamatan --</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Kelurahan/Desa</label>
                <select id="village_code" name="village_code" class="form-select">
                    <option value="">-- Pilih Kelurahan --</option>
                </select>
            </div>
        </div>
        <div class="mt-3">
            <label class="form-label">Alamat</label>
            <textarea name="address" class="form-control" rows="2"></textarea>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-end gap-2">
        <a href="/branches" class="btn btn-outline-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">Simpan</button>
    </div>
</form>
<script>
async function loadOptions(url, selectId) {
    const res = await fetch(url);
    const data = await res.json();
    const sel = document.getElementById(selectId);
    sel.innerHTML = '<option value="">-- Pilih --</option>';
    data.forEach(item => {
        const opt = document.createElement('option');
        opt.value = item.code;
        opt.textContent = item.name;
        sel.appendChild(opt);
    });
}
document.addEventListener('DOMContentLoaded', function() {
    loadOptions('/api/regions/provinces', 'province_code');
    document.getElementById('province_code').addEventListener('change', function() {
        loadOptions('/api/regions/regencies?province=' + this.value, 'regency_code');
    });
    document.getElementById('regency_code').addEventListener('change', function() {
        loadOptions('/api/regions/districts?regency=' + this.value, 'district_code');
    });
    document.getElementById('district_code').addEventListener('change', function() {
        loadOptions('/api/regions/villages?district=' + this.value, 'village_code');
    });
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/branches/create.blade.php ENDPATH**/ ?>