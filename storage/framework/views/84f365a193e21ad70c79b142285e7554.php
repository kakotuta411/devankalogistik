<?php $__env->startSection('title', 'Purchase Orders'); ?>
<?php $__env->startSection('page_header'); ?><h1 class="h2">Purchase Orders</h1><?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="mb-3">
    <a href="/purchase-orders/create" class="btn btn-primary">Buat PO Baru</a>
</div>
<table class="table table-hover align-middle">
    <thead>
        <tr>
            <th>No. PO</th>
            <th>Customer</th>
            <th>Total</th>
            <th>Dibayar</th>
            <th>Sisa</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
    <?php $__currentLoopData = $pos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $po): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td><a href="/purchase-orders/<?php echo e($po->id); ?>"><?php echo e($po->po_number); ?></a></td>
            <td><?php echo e($po->customer?->company_name ?? $po->customer?->name ?? '-'); ?></td>
            <td>Rp <?php echo e(number_format($po->total_amount ?? 0, 0, ',', '.')); ?></td>
            <td>Rp <?php echo e(number_format($po->paid_amount ?? 0, 0, ',', '.')); ?></td>
            <td>Rp <?php echo e(number_format(($po->total_amount ?? 0) - ($po->paid_amount ?? 0), 0, ',', '.')); ?></td>
            <td><?php echo e($po->status); ?></td>
            <td>
                <a href="/purchase-orders/<?php echo e($po->id); ?>/edit" class="btn btn-sm btn-outline-primary">Edit</a>
            </td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>
<?php echo e($pos->links()); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/purchase-orders/index.blade.php ENDPATH**/ ?>