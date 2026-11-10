<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Resi {{ $shipment->resi_no }}</title>
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
            font-size: 22px;
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

        /* Nomor resi / PO: kotak hitam rounded */
        .num-box {
            background: #000;
            color: #fff;
            border-radius: 8px;
            padding: 6px 14px;
            min-width: 112px;
        }

        .num-box .num-label {
            font-size: 8px;
            color: #bbb;
            letter-spacing: 0.5px;
        }

        .num-box .num-value {
            font-size: 12px;
            font-weight: bold;
            color: #fff;
        }

        /* Garis pemisah vertikal kiri - kanan */
        .divider-v {
            position: absolute;
            top: 106px;
            height: 216px;
            left: 452px;
            border-left: 1px solid #ccc;
        }
    </style>
</head>

<body>
    {{-- Logo -- top left (lebih tinggi & lebar) --}}
    @if($logoUrl)
        <div class="abs" style="left: 30px; top: 20px;">
            <img src="{{ $logoUrl }}" alt="Logo" style="max-height: 85px; max-width: 175px;">
        </div>
    @endif

    {{-- Title --}}
    <div class="abs title" style="left: 240px; top: 20px;">RESI PENGIRIMAN</div>

    {{-- NO. RESI (kotak hitam rounded) --}}
    <div class="abs num-box" style="left: 570px; top: 20px;">
        <div class="num-label">NO. RESI</div>
        <div class="num-value">{{ $shipment->resi_no }}</div>
    </div>

    {{-- NO. PO (kotak hitam rounded) --}}
    <div class="abs num-box" style="left: 730px; top: 20px;">
        <div class="num-label">NO. PO</div>
        <div class="num-value">{{ $shipment->po_number ?? '-' }}</div>
    </div>

    {{-- Company --}}
    <div class="abs company" style="left: 240px; top: 56px;">PT. DEVANKA LOGISTIK INDONESIA</div>

    {{-- Garis bawah header --}}
    <div class="abs" style="left: 20px; top: 98px; width: 872px; border-bottom: 1.5px solid #333;"></div>

    {{-- Garis pemisah vertikal antara kiri & kanan --}}
    <div class="divider-v"></div>

    {{-- ===== Kolom kiri: INFORMASI PENGIRIMAN ===== --}}
    <div class="abs section" style="left: 30px; top: 108px;">INFORMASI PENGIRIMAN</div>

    <div class="abs label" style="left: 30px; top: 136px;">Tanggal Masuk</div>
    <div class="abs" style="left: 170px; top: 136px;">:</div>
    <div class="abs value" style="left: 186px; top: 136px;">{{ $shipment->created_at?->format('d/m/Y H:i') }}</div>

    <div class="abs label" style="left: 30px; top: 162px;">Status</div>
    <div class="abs" style="left: 170px; top: 162px;">:</div>
    <div class="abs value" style="left: 186px; top: 162px;">{{ $shipment->shipment_status }}</div>

    <div class="abs label" style="left: 30px; top: 188px;">Jenis Layanan</div>
    <div class="abs" style="left: 170px; top: 188px;">:</div>
    <div class="abs value" style="left: 186px; top: 188px;">{{ $shipment->service_type }}</div>

    <div class="abs label" style="left: 30px; top: 214px;">Total Koli</div>
    <div class="abs" style="left: 170px; top: 214px;">:</div>
    <div class="abs value" style="left: 186px; top: 214px;">{{ $shipment->total_koli }}</div>

    <div class="abs label" style="left: 30px; top: 240px;">Total Berat</div>
    <div class="abs" style="left: 170px; top: 240px;">:</div>
    <div class="abs value" style="left: 186px; top: 240px;">{{ $shipment->total_weight }} kg</div>

    <div class="abs label" style="left: 30px; top: 266px;">Isi Barang</div>
    <div class="abs" style="left: 170px; top: 266px;">:</div>
    <div class="abs value maxw" style="left: 186px; top: 266px;">{{ $shipment->package_description ?? '-' }}</div>

    <div class="abs label" style="left: 30px; top: 300px;">Tipe Pembayaran</div>
    <div class="abs" style="left: 170px; top: 298px;">:</div>
    <div class="abs value" style="left: 186px; top: 297px;"><span
            class="badge">{{ strtoupper($shipment->payment_type ?? 'KREDIT') }}</span></div>

    {{-- ===== Kolom kanan: PENGIRIM ===== --}}
    <div class="abs section" style="left: 468px; top: 108px;">PENGIRIM</div>

    <div class="abs label" style="left: 472px; top: 136px;">Nama</div>
    <div class="abs" style="left: 553px; top: 136px;">:</div>
    <div class="abs value maxw-narrow" style="left: 565px; top: 136px;">{{ $shipment->sender_name }}</div>

    <div class="abs label" style="left: 472px; top: 160px;">Telepon</div>
    <div class="abs" style="left: 553px; top: 160px;">:</div>
    <div class="abs value maxw-narrow" style="left: 565px; top: 160px;">{{ $shipment->sender_phone }}</div>

    <div class="abs label" style="left: 472px; top: 184px;">Alamat</div>
    <div class="abs" style="left: 553px; top: 184px;">:</div>
    <div class="abs value maxw-narrow" style="left: 565px; top: 184px;">{{ $shipment->sender_address }}</div>

    {{-- ===== Kolom kanan: PENERIMA ===== --}}
    <div class="abs section" style="left: 468px; top: 222px;">PENERIMA</div>

    <div class="abs label" style="left: 472px; top: 250px;">Nama</div>
    <div class="abs" style="left: 553px; top: 250px;">:</div>
    <div class="abs value maxw-narrow" style="left: 565px; top: 250px;">{{ $shipment->receiver_name }}</div>

    <div class="abs label" style="left: 472px; top: 274px;">Telepon</div>
    <div class="abs" style="left: 553px; top: 274px;">:</div>
    <div class="abs value maxw-narrow" style="left: 565px; top: 274px;">{{ $shipment->receiver_phone }}</div>

    <div class="abs label" style="left: 472px; top: 298px;">Alamat</div>
    <div class="abs" style="left: 553px; top: 298px;">:</div>
    <div class="abs value maxw-narrow" style="left: 565px; top: 298px;">{{ $shipment->receiver_address }}</div>

    {{-- Footer tagline --}}
    <div class="abs tagline" style="left: 30px; top: 450px;">PARTNER LOGISTIK PROFESIONAL UNTUK BISNIS ANDA</div>
    <div class="abs small" style="left: 30px; top: 468px;">www.devankalogistik.com</div>

    {{-- Signature lines (space lebih banyak) --}}
    <div class="abs sig" style="left: 548px; top: 450px;">PENERIMA</div>
    <div class="abs sig" style="left: 750px; top: 450px;">PETUGAS</div>
    <div class="abs" style="left: 548px; top: 486px; width: 130px; border-top: 1px solid #333;"></div>
    <div class="abs" style="left: 750px; top: 486px; width: 130px; border-top: 1px solid #333;"></div>

    {{-- Bottom note --}}
    <div class="abs small" style="left: 23px; top: 486px;">Simpan resi ini sebagai bukti pengiriman.</div>
</body>

</html>