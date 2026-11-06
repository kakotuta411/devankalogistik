<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manifest <?php echo e($manifest->manifest_no); ?></title>
    <style>
        @page { size: <?php echo e(($printSettings['paper_format'] ?? 'A4') === 'thermal' ? '10cm 10cm' : (($printSettings['paper_format'] ?? 'A4') === 'dotmatrix-half' ? '241mm 140mm' : ($printSettings['paper_format'] ?? 'A4'))); ?>; margin: 1cm; }
        body { font-family: Arial, sans-serif; font-size: 10pt; padding-bottom: 40px; }
        .header { text-align: center; margin-bottom: 12px; border-bottom: 2px solid #000; padding-bottom: 8px; }
        .title { font-size: 14pt; font-weight: bold; margin: 0; letter-spacing: 1px; }
        .info { margin-bottom: 14px; }
        .info td { padding: 3px 6px; vertical-align: top; }
        .info td:first-child { font-weight: bold; width: 130px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 5px 6px; }
        th { background: #f0f0f0; text-align: center; }
        .note { margin-top: 10px; font-size: 9pt; font-style: italic; }
        .sign-wrap { margin-top: 30px; }
        .sign { display: inline-block; width: 48%; text-align: center; vertical-align: top; }
        .footer { position: fixed; bottom: 10px; left: 28px; right: 28px; text-align: center; font-size: 9pt; }
    </style>
</head>
<body>
    <div class="header">
        <?php if($logoUrl): ?>
            <img src="<?php echo e($logoUrl); ?>" style="max-height: 60px; max-width: 120px; margin-bottom: 8px;" alt="Logo"><br>
        <?php endif; ?>
        <div style="font-size: 11pt; font-weight: bold;"><?php echo e($printSettings['company_name'] ?? config('app.name')); ?></div>
        <?php if(!empty($printSettings['header_text'])): ?>
            <small><?php echo e($printSettings['header_text']); ?></small><br>
        <?php endif; ?>
        <div class="title" style="margin-top: 6px;">PACKING LIST / MANIFEST</div>
    </div>

    <table class="info" style="border: none;">
        <tr><td>Tanggal Muat</td><td>: <?php echo e($manifest->dispatch_time?->format('d F Y') ?? $manifest->created_at?->format('d F Y')); ?></td></tr>
        <tr><td>No Mobil</td><td>: <?php echo e($manifest->vehicle_no ?? '-'); ?></td></tr>
        <tr><td>Nama Supir</td><td>: <?php echo e($manifest->driver_name ?? '-'); ?></td></tr>
        <tr><td>Kota Asal</td><td>: <?php echo e($manifest->originBranch?->city ?? '-'); ?></td></tr>
        <tr><td>Tujuan</td><td>: <?php echo e($manifest->destinationBranch?->city ?? '-'); ?><?php echo e($manifest->destinationBranch?->name ? ', ' . $manifest->destinationBranch->name : ''); ?></td></tr>
    </table>

    <table>
        <thead>
            <tr>
                <th style="width: 32px;">No</th>
                <th>No Resi AKL</th>
                <th>Jenis Barang</th>
                <th>Detail Paket</th>
                <th>Tujuan</th>
                <th style="width: 50px;">Koli</th>
                <th>Penerima</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $manifest->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td style="text-align: center;"><?php echo e($i + 1); ?></td>
                <td><?php echo e($item->shipment?->resi_no); ?></td>
                <td><?php echo e($item->shipment?->service_type ?? '-'); ?></td>
                <td><?php echo e($item->shipment?->package_description ?? '-'); ?></td>
                <td><?php echo e($item->shipment?->destinationBranch?->city); ?></td>
                <td style="text-align: center;"><?php echo e($item->shipment?->total_koli); ?></td>
                <td><?php echo e($item->shipment?->receiver_name); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="7" style="text-align: center;">Tidak ada data</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="note">Note: Ongkos Jalan (Done)</div>

    <div class="sign-wrap" style="margin-top: 40px;">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="border: none; text-align: center; width: 33%; vertical-align: top;">
                    <p style="margin-bottom: 60px;"><?php echo e($manifest->originBranch?->city ?? ''); ?>, __________</p>
                    <p style="margin: 0; font-weight: bold;">Staff Branch</p>
                    <p style="margin: 0; font-size: 9pt;">( __________________ )</p>
                </td>
                <td style="border: none; text-align: center; width: 33%; vertical-align: top;">
                    <p style="margin-bottom: 60px;"><?php echo e($manifest->originBranch?->city ?? ''); ?>, __________</p>
                    <p style="margin: 0; font-weight: bold;">Driver</p>
                    <p style="margin: 0; font-size: 9pt;">( <?php echo e($manifest->driver_name ?? '________________'); ?> )</p>
                </td>
                <td style="border: none; text-align: center; width: 33%; vertical-align: top;">
                    <p style="margin-bottom: 60px;"><?php echo e($manifest->destinationBranch?->city ?? ''); ?>, __________</p>
                    <p style="margin: 0; font-weight: bold;">Penerima</p>
                    <p style="margin: 0; font-size: 9pt;">( __________________ )</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <?php if(!empty($printSettings['footer_text'])): ?> <?php echo e($printSettings['footer_text']); ?> <?php endif; ?>
    </div>
</body>
</html><?php /**PATH /var/www/html/resources/views/print/manifest-sheet.blade.php ENDPATH**/ ?>