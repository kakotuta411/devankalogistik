<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice <?php echo e($invoice->invoice_no); ?></title>
    <style>
        @page { size: <?php echo e(($printSettings['paper_format'] ?? 'A4') === 'thermal' ? '10cm 10cm' : (($printSettings['paper_format'] ?? 'A4') === 'dotmatrix-half' ? '241mm 140mm' : ($printSettings['paper_format'] ?? 'A4'))); ?>; margin: 1cm; }
        body { font-family: Arial, sans-serif; font-size: 10pt; padding-bottom: 50px; }
        .header { margin-bottom: 14px; border-bottom: 2px solid #000; padding-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th, td { border: 1px solid #000; padding: 5px 6px; }
        th { background: #f0f0f0; text-align: center; }
        .info td { padding: 3px 6px; vertical-align: top; }
        .info td:first-child { font-weight: bold; width: 120px; }
        .total td { font-weight: bold; }
        .text-right { text-align: right; }
        .grand { font-size: 11pt; }
        .footer { position: fixed; bottom: 10px; left: 28px; right: 28px; text-align: center; font-size: 9pt; }
    </style>
</head>
<body>
    <div class="header" style="display: table; width: 100%;">
        <div style="display: table-cell; width: 80px; vertical-align: middle;">
            <?php if($logoUrl): ?>
                <img src="<?php echo e($logoUrl); ?>" style="max-height: 70px; max-width: 120px; margin-right: 8px;" alt="Logo">
            <?php endif; ?>
        </div>
        <div style="display: table-cell; vertical-align: middle; text-align: right;">
            <div style="font-size: 12pt; font-weight: bold;"><?php echo e($printSettings['company_name'] ?? config('app.name')); ?></div>
            <?php if(!empty($printSettings['header_text'])): ?>
                <small><?php echo e($printSettings['header_text']); ?></small>
            <?php endif; ?>
        </div>
    </div>
    <h3 style="text-align: center; margin: 0 0 12px 0;">INVOICE</h3>

    <table class="info" style="border: none;">
        <tr><td>No Invoice</td><td>: <?php echo e($invoice->invoice_no); ?></td><td>Tanggal Invoice</td><td>: <?php echo e($invoice->invoice_date?->format('d/m/Y') ?? date('d/m/Y')); ?></td></tr>
        <tr><td>Cabang</td><td>: <?php echo e($invoice->branch?->name); ?></td><td>Due Date</td><td>: <?php echo e($invoice->due_date?->format('d/m/Y') ?? '-'); ?></td></tr>
        <tr><td>Customer</td><td>: <?php echo e($invoice->customer_name ?? '-'); ?></td><td>Telepon</td><td>: <?php echo e($invoice->customer_phone ?? '-'); ?></td></tr>
        <tr><td>Alamat</td><td colspan="3">:
            <?php echo e($invoice->customer_address ?? '-'); ?>

            <?php if($invoice->customer_village_code || $invoice->customer_district_code || $invoice->customer_regency_code || $invoice->customer_province_code): ?>
                <br>
                <?php echo e(\App\Models\Region::where('code', $invoice->customer_village_code)->value('name') ?? ''); ?>

                <?php echo e(\App\Models\Region::where('code', $invoice->customer_district_code)->value('name') ?? ''); ?>

                <?php echo e(\App\Models\Region::where('code', $invoice->customer_regency_code)->value('name') ?? ''); ?>

                <?php echo e(\App\Models\Region::where('code', $invoice->customer_province_code)->value('name') ?? ''); ?>

            <?php endif; ?>
        </td></tr>
        <tr><td>Email</td><td>: <?php echo e($invoice->customer_email ?? '-'); ?></td><td>Status</td><td>: <?php echo e($invoice->invoice_status); ?></td></tr>
    </table>

    <table>
        <thead>
            <tr>
                <th style="width: 32px;">No</th>
                <th>Resi</th>
                <th>Asal -&gt; Tujuan</th>
                <th style="width: 40px;">Koli</th>
                <th style="width: 60px;">Berat</th>
                <th style="width: 70px;">Layanan</th>
                <th style="width: 90px;">Harga</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $invoice->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td style="text-align: center;"><?php echo e($i + 1); ?></td>
                <td><?php echo e($item->shipment?->resi_no); ?></td>
                <td><?php echo e($item->shipment?->originBranch?->city); ?> -&gt; <?php echo e($item->shipment?->destinationBranch?->city); ?></td>
                <td style="text-align: center;"><?php echo e($item->shipment?->total_koli); ?></td>
                <td style="text-align: center;"><?php echo e($item->shipment?->total_weight); ?> kg</td>
                <td style="text-align: center;"><?php echo e($item->shipment?->service_type); ?></td>
                <td class="text-right">Rp <?php echo e(number_format(($item->shipment?->total_weight ?? 0) * (strtoupper($item->shipment?->service_type ?? '') === 'EXPRESS' ? (float)env('SHIPPING_RATE_EXPRESS', 15000) : (float)env('SHIPPING_RATE_REGULAR', 10000)), 0, ',', '.')); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="7" style="text-align: center;">Tidak ada data</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if($invoice->isChild()): ?>
        <table style="width: 50%; margin-left: auto;">
            <tr><td>Sisa dari invoice <?php echo e($invoice->parentInvoice?->invoice_no); ?></td><td class="text-right"></td></tr>
            <tr class="total grand"><td><strong>TOTAL</strong></td><td class="text-right"><strong>Rp <?php echo e(number_format($invoice->displayTotal(), 0, ',', '.')); ?></strong></td></tr>
        </table>
    <?php else: ?>
        <table style="width: 50%; margin-left: auto;">
            <tr><td>Biaya Pengiriman (<?php echo e(number_format($invoice->shipping_cost, 0, ',', '.')); ?>/kg x <?php echo e(number_format($invoice->total_weight, 2, ',', '.')); ?> kg)</td><td class="text-right">Rp <?php echo e(number_format($invoice->shipping_cost * $invoice->total_weight, 0, ',', '.')); ?></td></tr>
            <tr><td>Biaya Packaging</td><td class="text-right">Rp <?php echo e(number_format($invoice->packaging_cost, 0, ',', '.')); ?></td></tr>
            <tr><td>Charge Alat</td><td class="text-right">Rp <?php echo e(number_format($invoice->equipment_charge, 0, ',', '.')); ?></td></tr>
            <tr><td>Biaya Lain-lain</td><td class="text-right">Rp <?php echo e(number_format($invoice->other_costs, 0, ',', '.')); ?></td></tr>
            <tr><td>Diskon (<?php echo e(number_format($invoice->discount_value, 2, ',', '.')); ?>%)</td><td class="text-right">-Rp <?php echo e(number_format($invoice->discount, 0, ',', '.')); ?></td></tr>
            <tr><td>PPFTZ 03</td><td class="text-right">Rp <?php echo e(number_format($invoice->ppftz03, 0, ',', '.')); ?></td></tr>
            <tr><td>PPN (<?php echo e(number_format($invoice->ppn, 2, ',', '.')); ?>%)</td><td class="text-right">Rp <?php echo e(number_format($invoice->ppnAmount(), 0, ',', '.')); ?></td></tr>
            <tr><td>PPH (<?php echo e(number_format($invoice->pph, 2, ',', '.')); ?>%)</td><td class="text-right">Rp <?php echo e(number_format($invoice->pphAmount(), 0, ',', '.')); ?></td></tr>
            <tr class="total grand"><td><strong>TOTAL</strong></td><td class="text-right"><strong>Rp <?php echo e(number_format($invoice->displayTotal(), 0, ',', '.')); ?></strong></td></tr>
        </table>
    <?php endif; ?>

    <?php if($invoice->notes): ?>
        <div style="margin-top: 8px;"><strong>Catatan:</strong> <?php echo e($invoice->notes); ?></div>
    <?php endif; ?>

    <div class="footer">
        <?php if(!empty($printSettings['footer_text'])): ?> <?php echo e($printSettings['footer_text']); ?> <?php else: ?> Terima kasih atas kepercayaan Anda. <?php endif; ?>
    </div>
</body>
</html><?php /**PATH /var/www/html/resources/views/print/invoice.blade.php ENDPATH**/ ?>