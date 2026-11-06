<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Manifest List</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 4px; }
        th { background-color: #f2f2f2; font-weight: bold; }
        h2 { margin-bottom: 10px; }
    </style>
</head>
<body>
    <h2>Daftar Manifest</h2>
    <p>Tanggal cetak: <?php echo e($generated_at->format('d/m/Y H:i')); ?> | Total: <?php echo e($manifests->count()); ?> manifest</p>
    <table>
        <thead>
            <tr>
                <th>No. Manifest</th>
                <th>Asal</th>
                <th>Tujuan</th>
                <th>Status</th>
                <th>No. Kendaraan</th>
                <th>Driver</th>
                <th>Dibuat</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $manifests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($m->manifest_no); ?></td>
                <td><?php echo e($m->originBranch?->city ?? '-'); ?></td>
                <td><?php echo e($m->destinationBranch?->city ?? '-'); ?></td>
                <td><?php echo e($m->manifest_status); ?></td>
                <td><?php echo e($m->vehicle_no ?? '-'); ?></td>
                <td><?php echo e($m->driver_name ?? '-'); ?></td>
                <td><?php echo e($m->created_at?->format('d/m/Y H:i')); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</body>
</html>
<?php /**PATH /var/www/html/resources/views/print/manifest-list.blade.php ENDPATH**/ ?>