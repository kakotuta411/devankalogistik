<?php $__env->startSection('title', 'Edit Shipment'); ?>
<?php $__env->startSection('page_header'); ?><h1 class="h2">Edit Resi: <?php echo e($shipment->resi_no); ?></h1><?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<form action="/shipments/<?php echo e($shipment->id); ?>" method="POST" class="card">
    <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
    <div class="card-body">
        <h4 class="mb-3">Data Pengirim</h4>
        <div class="row g-3">
            <div class="col-12">
                <label for="customer_id" class="form-label">Customer (Perusahaan)</label>
                <select name="customer_id" class="form-select" id="customer_id" style="max-height: 200px; overflow-y: auto;" data-selected="<?php echo e($shipment->customer_id); ?>">
                    <option value="">-- Pilih Customer --</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Nama Pengirim</label>
                <input type="text" name="sender_name" value="<?php echo e($shipment->sender_name); ?>" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">Telepon Pengirim</label>
                <input type="text" name="sender_phone" value="<?php echo e($shipment->sender_phone); ?>" class="form-control" inputmode="numeric" pattern="[0-9]*" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
            </div>
        </div>
        <div class="mt-3">
            <label class="form-label">Alamat Pengirim</label>
            <textarea name="sender_address" class="form-control" rows="2"><?php echo e($shipment->sender_address); ?></textarea>
        </div>

        <hr class="my-4">
        <h4 class="mb-3">Wilayah Pengirim</h4>
        <div class="row g-3">
            <div class="col-md-5">
                <label for="sender_province" class="form-label">Provinsi</label>
                <select name="sender_province_code" class="form-control" id="sender_province" data-selected="<?php echo e($shipment->sender_province_code); ?>">
                    <option value="">Pilih...</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="sender_regency" class="form-label">Kab/Kota</label>
                <select name="sender_regency_code" class="form-control" id="sender_regency" data-selected="<?php echo e($shipment->sender_regency_code); ?>">
                    <option value="">Pilih...</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="sender_district" class="form-label">Kecamatan</label>
                <select name="sender_district_code" class="form-control" id="sender_district" data-selected="<?php echo e($shipment->sender_district_code); ?>">
                    <option value="">Pilih...</option>
                </select>
            </div>
            <div class="col-12">
                <label for="sender_village" class="form-label">Kelurahan/Desa</label>
                <select name="sender_village_code" class="form-control" id="sender_village" data-selected="<?php echo e($shipment->sender_village_code); ?>">
                    <option value="">Pilih...</option>
                </select>
            </div>
        </div>

        <hr class="my-4">
        <h4 class="mb-3">Data Penerima</h4>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Nama Penerima</label>
                <input type="text" name="receiver_name" value="<?php echo e($shipment->receiver_name); ?>" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">Telepon Penerima</label>
                <input type="text" name="receiver_phone" value="<?php echo e($shipment->receiver_phone); ?>" class="form-control" inputmode="numeric" pattern="[0-9]*" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
            </div>
        </div>
        <div class="mt-3">
            <label class="form-label">Alamat Penerima</label>
            <textarea name="receiver_address" class="form-control" rows="2"><?php echo e($shipment->receiver_address); ?></textarea>
        </div>

        <hr class="my-4">
        <h4 class="mb-3">Wilayah Penerima</h4>
        <div class="row g-3">
            <div class="col-md-5">
                <label for="receiver_province" class="form-label">Provinsi</label>
                <select name="receiver_province_code" class="form-control" id="receiver_province" data-selected="<?php echo e($shipment->receiver_province_code); ?>">
                    <option value="">Pilih...</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="receiver_regency" class="form-label">Kab/Kota</label>
                <select name="receiver_regency_code" class="form-control" id="receiver_regency" data-selected="<?php echo e($shipment->receiver_regency_code); ?>">
                    <option value="">Pilih...</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="receiver_district" class="form-label">Kecamatan</label>
                <select name="receiver_district_code" class="form-control" id="receiver_district" data-selected="<?php echo e($shipment->receiver_district_code); ?>">
                    <option value="">Pilih...</option>
                </select>
            </div>
            <div class="col-12">
                <label for="receiver_village" class="form-label">Kelurahan/Desa</label>
                <select name="receiver_village_code" class="form-control" id="receiver_village" data-selected="<?php echo e($shipment->receiver_village_code); ?>">
                    <option value="">Pilih...</option>
                </select>
            </div>
        </div>

        <hr class="my-4">
        <h4 class="mb-3">Pengiriman</h4>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Cabang Tujuan *</label>
                <select name="destination_branch_id" class="form-select" required>
                    <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($b->id); ?>" <?php echo e($shipment->destination_branch_id === $b->id ? 'selected' : ''); ?>><?php echo e($b->branch_code); ?> — <?php echo e($b->name); ?> (<?php echo e($b->city); ?>)</option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Jenis Layanan</label>
                <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
                    <input type="radio" class="btn-check" name="service_type" id="service_reguler" value="REGULER" <?php echo e($shipment->service_type === 'REGULER' ? 'checked' : ''); ?> autocomplete="off">
                    <label class="btn btn-outline-primary" for="service_reguler">REGULER</label>
                    <input type="radio" class="btn-check" name="service_type" id="service_express" value="EXPRESS" <?php echo e($shipment->service_type === 'EXPRESS' ? 'checked' : ''); ?> autocomplete="off">
                    <label class="btn btn-outline-primary" for="service_express">EXPRESS</label>
                </div>
            </div>
            <div class="col-md-6">
                <label for="po_number" class="form-label">Nomor PO</label>
                <input type="text" name="po_number" class="form-control" id="po_number" value="<?php echo e($shipment->po_number); ?>" placeholder="Nomor PO dari customer">
            </div>
            <div class="col-12">
                <label class="form-label">Tipe Pembayaran</label>
                <div class="btn-group" role="group">
                    <input type="radio" class="btn-check" name="payment_type" id="pay_kredit" value="kredit" <?php echo e($shipment->payment_type === 'kredit' ? 'checked' : ''); ?> autocomplete="off">
                    <label class="btn btn-outline-secondary" for="pay_kredit">KREDIT</label>
                    <input type="radio" class="btn-check" name="payment_type" id="pay_lunas" value="lunas" <?php echo e($shipment->payment_type === 'lunas' ? 'checked' : ''); ?> autocomplete="off">
                    <label class="btn btn-outline-secondary" for="pay_lunas">LUNAS</label>
                    <input type="radio" class="btn-check" name="payment_type" id="pay_cod" value="cod" <?php echo e($shipment->payment_type === 'cod' ? 'checked' : ''); ?> autocomplete="off">
                    <label class="btn btn-outline-secondary" for="pay_cod">COD</label>
                </div>
            </div>
        </div>

        <hr class="my-4">
        <h4 class="mb-3">Detail Paket</h4>
        <div class="row g-3">
            <div class="col-md-12">
                <label class="form-label">Deskripsi Paket</label>
                <input type="text" name="package_description" value="<?php echo e($shipment->package_description); ?>" class="form-control" placeholder="Isi paket">
            </div>
            <div class="col-md-6">
                <label class="form-label">Total Koli</label>
                <input type="number" name="total_koli" value="<?php echo e($shipment->total_koli); ?>" class="form-control" min="1">
            </div>
            <div class="col-md-6">
                <label class="form-label">Berat (kg)</label>
                <input type="number" name="total_weight" step="0.01" value="<?php echo e($shipment->total_weight); ?>" class="form-control">
            </div>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-end gap-2">
        <a href="/shipments" class="btn btn-outline-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">Update</button>
    </div>
</form>

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

    ['sender', 'receiver'].forEach(function(prefix) {
        const provSel = document.getElementById(prefix + '_province');
        const regSel = document.getElementById(prefix + '_regency');
        const distSel = document.getElementById(prefix + '_district');
        const villSel = document.getElementById(prefix + '_village');
        if (!provSel) return;
        const selProv = provSel.getAttribute('data-selected') || '';
        const selReg = regSel ? (regSel.getAttribute('data-selected') || '') : '';
        const selDist = distSel ? (distSel.getAttribute('data-selected') || '') : '';
        const selVill = villSel ? (villSel.getAttribute('data-selected') || '') : '';

        if (selProv) {
            setTimeout(function() {
                provSel.value = selProv;
                loadOptions('/api/regions/regencies?province=' + selProv, prefix + '_regency');
                if (selReg) {
                    setTimeout(function() {
                        regSel.value = selReg;
                        loadOptions('/api/regions/districts?regency=' + selReg, prefix + '_district');
                        if (selDist) {
                            setTimeout(function() {
                                distSel.value = selDist;
                                loadOptions('/api/regions/villages?district=' + selDist, prefix + '_village');
                                if (selVill) {
                                    setTimeout(function() {
                                        villSel.value = selVill;
                                    }, 500);
                                }
                            }, 500);
                        }
                    }, 500);
                }
            }, 500);
        }
    });

    // Load customers into dropdown
    fetch('/api/customers/search?q=')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var sel = document.getElementById('customer_id');
            if (!sel) return;
            var selectedId = sel.getAttribute('data-selected') || '';
            sel.innerHTML = '<option value="">-- Pilih Customer --</option>';
            data.forEach(function(c) {
                var opt = document.createElement('option');
                opt.value = c.id;
                opt.textContent = (c.company_name || c.name) + (c.phone ? ' - ' + c.phone : '');
                if (c.id === selectedId) opt.selected = true;
                sel.appendChild(opt);
            });
        });
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/shipments/edit.blade.php ENDPATH**/ ?>