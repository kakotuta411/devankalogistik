<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Resi <?php echo e($shipment->resi_no); ?></title>
    <style>
        @page {
            size: 10cm 14cm;
            margin: 0.2cm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 9pt;
            margin: 0;
        }

        .wrap {
            border: 1px solid #000;
            padding: 2px;
        }

        .top {
            text-align: center;
            margin-bottom: 4px;
        }

        .svc {
            font-size: 13pt;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .meta {
            font-size: 8pt;
            color: #333;
        }

        .resi {
            text-align: center;
            font-size: 16pt;
            font-weight: bold;
            margin: 2px 0;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 4px 0;
        }

        .qr {
            text-align: center;
            margin: 2px 0;
        }

        .route {
            text-align: center;
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .block {
            margin-bottom: 2px;
            margin-left: 3px;
        }

        .lbl {
            font-weight: bold;
            font-size: 8pt;
            text-transform: uppercase;
            color: #555;
        }

        .name {
            font-weight: bold;
            font-size: 10pt;
        }

        .phone {
            font-size: 9pt;
        }

        .addr {
            font-size: 8.5pt;
            line-height: 1.3;
        }

        .row {
            display: flex;
            justify-content: space-between;
            gap: 4px;
            margin-top: 2px;
        }

        .box {
            flex: 1;
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
        }

        .box .k {
            font-size: 8pt;
            color: #555;
        }

        .box .v {
            font-size: 12pt;
            font-weight: bold;
        }

        .footer {
            text-align: center;
            font-size: 8pt;
            margin-top: 0;
            border-top: 1px solid #ccc;
            padding-top: 4px;
        }
    </style>
</head>

<body>
    <?php for($koli = 1; $koli <= $shipment->total_koli; $koli++): ?>
    <div class="wrap">
        <div style="text-align: center; font-weight: bold; font-size: 11pt; margin-bottom: 4px;">KOLI <?php echo e($koli); ?> / <?php echo e($shipment->total_koli); ?></div>
        <div class="top">
            <?php if($logoUrl): ?>
                <img src="<?php echo e($logoUrl); ?>" style="max-height: 36px; max-width: 100%; margin-bottom: 6px;" alt="Logo"><br>
            <?php endif; ?>
            <!-- <strong><?php echo e($printSettings['company_name'] ?? config('app.name')); ?></strong><br>
            <span class="meta"><?php echo e($shipment->created_at?->format('d M Y, H:i')); ?></span><br> -->
            <span
                class="svc"><?php echo e(strtoupper($shipment->service_type ?? 'REGULER ')); ?><?php echo e($shipment->created_at?->format(' d M Y, H:i')); ?></span>
        </div>
        <div class="resi"><?php echo e($shipment->resi_no); ?></div>
        <div class="qr">
            <img src="<?php echo e($qrBase64); ?>" alt="QR" style="width: 70px; height: 70px;">
        </div>
        <div class="route"><?php echo e(strtoupper($shipment->originBranch?->city ?? '')); ?> -
            <?php echo e(strtoupper($shipment->destinationBranch?->city ?? '')); ?>

        </div>

        <div class="block">
            <div class="lbl">Penerima</div>
            <div class="name"><?php echo e($shipment->receiver_name); ?></div>
            <div class="phone"><?php echo e($shipment->receiver_phone); ?></div>
            <div class="addr">
                <?php echo e($shipment->receiver_address); ?>

                <?php if($shipment->receiver_village_code || $shipment->receiver_district_code || $shipment->receiver_regency_code || $shipment->receiver_province_code): ?>
                    <br>
                    <?php echo e(strtolower(\App\Models\Region::where('code', $shipment->receiver_province_code)->value('name') ?? '')); ?>,
                    <?php echo e(strtolower(\App\Models\Region::where('code', $shipment->receiver_regency_code)->value('name') ?? '')); ?>,
                    <?php echo e(strtolower(\App\Models\Region::where('code', $shipment->receiver_village_code)->value('name') ?? '')); ?>

                <?php endif; ?>
            </div>
        </div>

        <div class="block">
            <div class="lbl">Pengirim</div>
            <div class="name"><?php echo e($shipment->sender_name); ?></div>
            <div class="phone"><?php echo e($shipment->sender_phone); ?></div>
            <div class="addr">
                <?php echo e($shipment->sender_address); ?>

                <?php if($shipment->sender_village_code || $shipment->sender_district_code || $shipment->sender_regency_code || $shipment->sender_province_code): ?>
                    <br>
                    <?php echo e(strtolower(\App\Models\Region::where('code', $shipment->sender_province_code)->value('name') ?? '')); ?>,
                    <?php echo e(strtolower(\App\Models\Region::where('code', $shipment->sender_regency_code)->value('name') ?? '')); ?>,
                    <?php echo e(strtolower(\App\Models\Region::where('code', $shipment->sender_village_code)->value('name') ?? '')); ?>

                <?php endif; ?>
            </div>
        </div>

        <div class="block">
            <div class="lbl">Isi Barang</div>
            <div><?php echo e($shipment->package_description ?? '-'); ?></div>
        </div>

        <div class="row">
            <div class="box">
                <div class="k">Qty/Koli</div>
                <div class="v"><?php echo e($shipment->total_koli); ?></div>
            </div>
            <div class="box">
                <div class="k">Berat</div>
                <div class="v"><?php echo e($shipment->total_weight); ?> kg</div>
            </div>
        </div>

        <div class="footer">
            <?php echo e($printSettings['footer_text'] ?? 'AKL LOGISTIK - Simpan resi ini sebagai bukti pengiriman'); ?>

        </div>
    </div>
    <?php if($koli < $shipment->total_koli): ?>
    <div style="page-break-after: always;"></div>
    <?php endif; ?>
    <?php endfor; ?>
</body>

</html><?php /**PATH /var/www/html/resources/views/print/resi-label.blade.php ENDPATH**/ ?>