<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Resi <?php echo e($shipment->resi_no); ?></title>
    <style>
        @page {
            size: 9.5in 5.5in;
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
            position: relative;
            width: 912px;
            height: 528px;
        }

        .abs {
            position: absolute;
        }

        .title {
            font-size: 20px;
            font-weight: bold;
        }

        .company {
            font-size: 11px;
            color: #333;
        }

        .label {
            font-size: 10px;
        }

        .value {
            font-size: 10px;
        }

        .section {
            font-size: 12px;
            font-weight: bold;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 10px;
            background: #fff3cd;
            color: #856404;
        }

        .tagline {
            font-size: 9px;
            color: #555;
        }

        .small {
            font-size: 8px;
            color: #888;
        }

        .sig {
            font-size: 9px;
        }

        .maxw {
            max-width: 300px;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .maxw-narrow {
            max-width: 200px;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
    </style>
</head>

<body>
    
    <?php if($logoUrl): ?>
        <div class="abs" style="left: 20px; top: 15px;">
            <img src="<?php echo e($logoUrl); ?>" alt="Logo" style="max-height: 60px; max-width: 120px;">
        </div>
    <?php endif; ?>

    
    <div class="abs title" style="left: 187px; top: 20px;">RESI PENGIRIMAN</div>

    
    <div class="abs label" style="left: 587px; top: 28px;">NO. RESI</div>
    <div class="abs value" style="left: 582px; top: 48px; font-weight: bold; font-size: 12px;"><?php echo e($shipment->resi_no); ?>

    </div>

    
    <div class="abs label" style="left: 771px; top: 28px;">NO. PO</div>
    <div class="abs value" style="left: 772px; top: 48px; font-weight: bold; font-size: 12px;">
        <?php echo e($shipment->po_number ?? '-'); ?></div>

    
    <div class="abs company" style="left: 190px; top: 55px;">PT. DEVANKA LOGISTIK INDONESIA</div>

    
    <div class="abs section" style="left: 21px; top: 104px;">INFORMASI PENGIRIMAN</div>

    
    <div class="abs label" style="left: 24px; top: 149px;">Tanggal Masuk</div>
    <div class="abs" style="left: 190px; top: 149px;">:</div>
    <div class="abs value" style="left: 206px; top: 149px;"><?php echo e($shipment->created_at?->format('d/m/Y H:i')); ?></div>

    
    <div class="abs label" style="left: 24px; top: 184px;">Status</div>
    <div class="abs" style="left: 190px; top: 184px;">:</div>
    <div class="abs value" style="left: 206px; top: 184px;"><?php echo e($shipment->shipment_status); ?></div>

    
    <div class="abs label" style="left: 24px; top: 219px;">Jenis Layanan</div>
    <div class="abs" style="left: 190px; top: 219px;">:</div>
    <div class="abs value" style="left: 206px; top: 219px;"><?php echo e($shipment->service_type); ?></div>

    
    <div class="abs label" style="left: 24px; top: 254px;">Total Koli</div>
    <div class="abs" style="left: 190px; top: 254px;">:</div>
    <div class="abs value" style="left: 206px; top: 254px;"><?php echo e($shipment->total_koli); ?></div>

    
    <div class="abs label" style="left: 24px; top: 289px;">Total Berat</div>
    <div class="abs" style="left: 190px; top: 289px;">:</div>
    <div class="abs value" style="left: 206px; top: 289px;"><?php echo e($shipment->total_weight); ?> kg</div>

    
    <div class="abs label" style="left: 24px; top: 324px;">Isi Barang</div>
    <div class="abs" style="left: 190px; top: 324px;">:</div>
    <div class="abs value maxw" style="left: 206px; top: 324px;"><?php echo e($shipment->package_description ?? '-'); ?></div>

    
    <div class="abs label" style="left: 24px; top: 394px;">Tipe Pembayaran</div>
    <div class="abs" style="left: 190px; top: 394px;">:</div>
    <div class="abs" style="left: 206px; top: 391px;"><span
            class="badge"><?php echo e(strtoupper($shipment->payment_type ?? 'KREDIT')); ?></span></div>

    
    <div class="abs section" style="left: 468px; top: 147px;">PENGIRIM</div>

    <div class="abs label" style="left: 472px; top: 182px;">Nama</div>
    <div class="abs" style="left: 553px; top: 181px;">:</div>
    <div class="abs value maxw-narrow" style="left: 565px; top: 181px;"><?php echo e($shipment->sender_name); ?></div>

    <div class="abs label" style="left: 472px; top: 211px;">Telepon</div>
    <div class="abs" style="left: 553px; top: 210px;">:</div>
    <div class="abs value maxw-narrow" style="left: 565px; top: 210px;"><?php echo e($shipment->sender_phone); ?></div>

    <div class="abs label" style="left: 472px; top: 240px;">Alamat</div>
    <div class="abs" style="left: 553px; top: 238px;">:</div>
    <div class="abs value maxw-narrow" style="left: 565px; top: 238px;"><?php echo e($shipment->sender_address); ?></div>

    
    <div class="abs section" style="left: 468px; top: 280px;">PENERIMA</div>

    <div class="abs label" style="left: 472px; top: 315px;">Nama</div>
    <div class="abs" style="left: 553px; top: 314px;">:</div>
    <div class="abs value maxw-narrow" style="left: 565px; top: 314px;"><?php echo e($shipment->receiver_name); ?></div>

    <div class="abs label" style="left: 472px; top: 344px;">Telepon</div>
    <div class="abs" style="left: 553px; top: 343px;">:</div>
    <div class="abs value maxw-narrow" style="left: 565px; top: 343px;"><?php echo e($shipment->receiver_phone); ?></div>

    <div class="abs label" style="left: 472px; top: 373px;">Alamat</div>
    <div class="abs" style="left: 553px; top: 372px;">:</div>
    <div class="abs value maxw-narrow" style="left: 565px; top: 372px;"><?php echo e($shipment->receiver_address); ?></div>

    
    <div class="abs tagline" style="left: 24px; top: 424px;">PARTNER LOGISTIK PROFESIONAL UNTUK BISNIS ANDA</div>
    <div class="abs small" style="left: 24px; top: 443px;">www.devankalogistik.com</div>

    
    <div class="abs sig" style="left: 548px; top: 435px;">PENERIMA</div>
    <div class="abs sig" style="left: 765px; top: 435px;">PETUGAS</div>

    
    <div class="abs small" style="left: 23px; top: 480px;">Simpan resi ini sebagai bukti pengiriman.</div>
</body>

</html><?php /**PATH /var/www/html/resources/views/print/resi-detail.blade.php ENDPATH**/ ?>