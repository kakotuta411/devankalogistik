<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice List</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 4px; }
        th { background-color: #f2f2f2; font-weight: bold; }
        h2 { margin-bottom: 10px; }
    </style>
</head>
<body>
    <h2>Daftar Invoice</h2>
    <p>Tanggal cetak: <?php echo e($generated_at->format('d/m/Y H:i')); ?> | Total: <?php echo e($invoices->count()); ?> invoice</p>
    <table>
        <thead>
            <tr>
                <th>No. Invoice</th>
                <th>Pelanggan</th>
                <th>Cabang</th>
                <th>Status</th>
                <th>Subtotal</th>
                <th>Dibuat</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($inv->invoice_no); ?></td>
                <td><?php echo e($inv->customer_name); ?></td>
                <td><?php echo e($inv->branch?->city ?? '-'); ?></td>
                <td><?php echo e($inv->invoice_status); ?></td>
                <td><?php echo e(number_format($inv->subtotal, 0, ',', '.')); ?></td>
                <td><?php echo e($inv->created_at?->format('d/m/Y H:i')); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</body>
</html>
<?php /**PATH /var/www/html/resources/views/print/invoice-list.blade.php ENDPATH**/ ?>