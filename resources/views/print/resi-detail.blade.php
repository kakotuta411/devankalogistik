<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Resi {{ $shipment->resi_no }}</title>
    <style>
        @page { size: A5; margin: 1cm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 10px; }
        .header h2 { margin: 0; font-size: 16px; }
        .header p { margin: 2px 0; font-size: 10px; color: #555; }
        .section { margin-bottom: 10px; }
        .section h4 { font-size: 12px; margin: 0 0 4px 0; border-bottom: 1px solid #ccc; padding-bottom: 2px; }
        .row { display: flex; justify-content: space-between; margin-bottom: 2px; }
        .row .label { font-weight: bold; width: 40%; }
        .row .value { width: 60%; text-align: right; }
        .payment-badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-weight: bold; }
        .kredit { background: #fff3cd; color: #856404; }
        .lunas { background: #d4edda; color: #155724; }
        .cod { background: #cce5ff; color: #004085; }
        .footer { margin-top: 15px; font-size: 9px; color: #888; text-align: center; border-top: 1px solid #ccc; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>RESI PENGIRIMAN</h2>
        <p>{{ $shipment->originBranch?->name ?? 'Logistik' }} → {{ $shipment->destinationBranch?->name ?? '-' }}</p>
        <p>No. Resi: <strong>{{ $shipment->resi_no }}</strong></p>
    </div>

    <div class="section">
        <h4>Informasi Pengiriman</h4>
        <div class="row"><span class="label">Tanggal Masuk</span><span class="value">{{ $shipment->created_at?->format('d/m/Y H:i') }}</span></div>
        <div class="row"><span class="label">Status</span><span class="value">{{ $shipment->shipment_status }}</span></div>
        <div class="row"><span class="label">Jenis Layanan</span><span class="value">{{ $shipment->service_type }}</span></div>
        <div class="row"><span class="label">Total Koli</span><span class="value">{{ $shipment->total_koli }}</span></div>
        <div class="row"><span class="label">Total Berat</span><span class="value">{{ $shipment->total_weight }} kg</span></div>
        @if($shipment->package_description)
        <div class="row"><span class="label">Isi Barang</span><span class="value">{{ $shipment->package_description }}</span></div>
        @endif
        @if($shipment->po_number)
        <div class="row"><span class="label">Nomor PO</span><span class="value">{{ $shipment->po_number }}</span></div>
        @endif
        <div class="row"><span class="label">Tipe Pembayaran</span><span class="value"><span class="payment-badge {{ $shipment->payment_type ?? 'kredit' }}">{{ strtoupper($shipment->payment_type ?? 'KREDIT') }}</span></span></div>
    </div>

    <div class="section">
        <h4>Pengirim</h4>
        <div class="row"><span class="label">Nama</span><span class="value">{{ $shipment->sender_name }}</span></div>
        <div class="row"><span class="label">Telepon</span><span class="value">{{ $shipment->sender_phone }}</span></div>
        <div class="row"><span class="label">Alamat</span><span class="value">{{ $shipment->sender_address }}</span></div>
    </div>

    <div class="section">
        <h4>Penerima</h4>
        <div class="row"><span class="label">Nama</span><span class="value">{{ $shipment->receiver_name }}</span></div>
        <div class="row"><span class="label">Telepon</span><span class="value">{{ $shipment->receiver_phone }}</span></div>
        <div class="row"><span class="label">Alamat</span><span class="value">{{ $shipment->receiver_address }}</span></div>
    </div>

    <div class="footer">
        <p>Dokumen ini dicetak secara otomatis. Simpan resi ini sebagai bukti pengiriman.</p>
    </div>
</body>
</html>
