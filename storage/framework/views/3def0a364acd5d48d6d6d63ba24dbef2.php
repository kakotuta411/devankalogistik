<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Shipment List</title>
    <style>
        @page { size: A4; margin: 1cm; }
        body { font-family: Arial, sans-serif; font-size: 9pt; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 4px; }
        th { background: #f0f0f0; }
        h2 { text-align: center; }
    </style>
</head>
<body>
    <h2>DAFTAR SHIPMENT</h2>
    <p>Generated: <?php echo e($generated_at->format('d/m/Y H:i')); ?></p>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Resi</th>
                <th>Asal</th>
                <th>Tujuan</th>
                <th>Koli</th>
                <th>Berat</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $shipments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($i + 1); ?></td>
                <td><?php echo e($s->resi_no); ?></td>
                <td><?php echo e($s->originBranch?->city); ?></td>
                <td><?php echo e($s->destinationBranch?->city); ?></td>
                <td><?php echo e($s->total_koli); ?></td>
                <td><?php echo e($s->total_weight); ?></td>
                <td><?php echo e($s->shipment_status); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</body>
</html>
<?php /**PATH /var/www/html/resources/views/print/shipment-list.blade.php ENDPATH**/ ?>