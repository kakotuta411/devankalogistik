<?php $__env->startSection('title', 'Buat PO'); ?>
<?php $__env->startSection('page_header'); ?><h1 class="h2">Buat PO Baru</h1><?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<form action="/purchase-orders" method="POST">
    <?php echo csrf_field(); ?>
    <div class="row g-3">
        <div class="col-md-6">
            <label for="po_number" class="form-label">Nomor PO</label>
            <input type="text" name="po_number" class="form-control" id="po_number" required>
        </div>
        <div class="col-md-6">
            <label for="customer_id" class="form-label">Customer</label>
            <select name="customer_id" class="form-select" id="customer_id" required>
                <option value="">-- Pilih Customer --</option>
                <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($c->id); ?>"><?php echo e($c->company_name ?? $c->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="col-md-6">
            <label for="total_amount" class="form-label">Total Amount</label>
            <input type="number" name="total_amount" class="form-control" id="total_amount" step="0.01" value="0">
        </div>
        <div class="col-12">
            <label for="notes" class="form-label">Catatan</label>
            <textarea name="notes" class="form-control" id="notes" rows="2"></textarea>
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="/purchase-orders" class="btn btn-secondary">Batal</a>
        </div>
    </div>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/purchase-orders/create.blade.php ENDPATH**/ ?>