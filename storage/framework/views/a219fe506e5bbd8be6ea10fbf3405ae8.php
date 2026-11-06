<?php $__env->startSection('title', 'Create Manifest'); ?>
<?php $__env->startSection('page_header'); ?><h1 class="h2">Buat Manifest</h1><?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<form action="/manifests" method="POST" data-bs-validate="" novalidate="" id="manifestForm">
    <?php echo csrf_field(); ?>
    <div class="row g-5">
        <div class="col-md-7 col-lg-8">
            <h4 class="mb-3">Data Manifest</h4>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="destination_branch_id" class="form-label">Cabang Tujuan *</label>
                    <select name="destination_branch_id" class="form-control" id="destination_branch_id" required>
                        <option value="">Pilih cabang tujuan...</option>
                        <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($b->id); ?>"><?php echo e($b->branch_code); ?> — <?php echo e($b->name); ?> (<?php echo e($b->city); ?>)</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                <?php $__empty_1 = true; $__currentLoopData = $availableShipments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branchId => $shipments): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php ($destBranch = \App\Models\Branch::find($branchId)); ?>
                <div class="mb-3">
                    <h6 class="text-primary"><?php echo e($destBranch?->branch_code); ?> — <?php echo e($destBranch?->name); ?> (<?php echo e($destBranch?->city); ?>)</h6>
                    <?php $__currentLoopData = $shipments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="form-field mb-1 ms-3">
                        <input type="checkbox" name="shipment_ids[]" value="<?php echo e($s->id); ?>" class="check dest-<?php echo e($branchId); ?>" id="ship_<?php echo e($s->id); ?>">
                        <label for="ship_<?php echo e($s->id); ?>"><?php echo e($s->resi_no); ?> — <?php echo e($s->sender_name); ?> → <?php echo e($s->receiver_name); ?></label>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-body-secondary small">Tidak ada shipment yang tersedia</p>
                <?php endif; ?>
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
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
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
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/manifests/create.blade.php ENDPATH**/ ?>