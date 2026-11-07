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
    {{-- Logo -- top left --}}
    @if($logoUrl)
        <div class="abs" style="left: 20px; top: 15px;">
            <img src="{{ $logoUrl }}" alt="Logo" style="max-height: 60px; max-width: 120px;">
        </div>
    @endif

    {{-- Title --}}
    <div class="abs title" style="left: 187px; top: 20px;">RESI PENGIRIMAN</div>

    {{-- NO. RESI --}}
    <div class="abs label" style="left: 587px; top: 28px;">NO. RESI</div>
    <div class="abs value" style="left: 582px; top: 48px; font-weight: bold; font-size: 12px;">{{ $shipment->resi_no }}
    </div>

    {{-- NO. PO --}}
    <div class="abs label" style="left: 771px; top: 28px;">NO. PO</div>
    <div class="abs value" style="left: 772px; top: 48px; font-weight: bold; font-size: 12px;">
        {{ $shipment->po_number ?? '-' }}</div>

    {{-- Company --}}
    <div class="abs company" style="left: 190px; top: 55px;">PT. DEVANKA LOGISTIK INDONESIA</div>

    {{-- Section: INFORMASI PENGIRIMAN --}}
    <div class="abs section" style="left: 21px; top: 104px;">INFORMASI PENGIRIMAN</div>

    {{-- Tanggal Masuk --}}
    <div class="abs label" style="left: 24px; top: 149px;">Tanggal Masuk</div>
    <div class="abs" style="left: 190px; top: 149px;">:</div>
    <div class="abs value" style="left: 206px; top: 149px;">{{ $shipment->created_at?->format('d/m/Y H:i') }}</div>

    {{-- Status --}}
    <div class="abs label" style="left: 24px; top: 184px;">Status</div>
    <div class="abs" style="left: 190px; top: 184px;">:</div>
    <div class="abs value" style="left: 206px; top: 184px;">{{ $shipment->shipment_status }}</div>

    {{-- Jenis Layanan --}}
    <div class="abs label" style="left: 24px; top: 219px;">Jenis Layanan</div>
    <div class="abs" style="left: 190px; top: 219px;">:</div>
    <div class="abs value" style="left: 206px; top: 219px;">{{ $shipment->service_type }}</div>

    {{-- Total Koli --}}
    <div class="abs label" style="left: 24px; top: 254px;">Total Koli</div>
    <div class="abs" style="left: 190px; top: 254px;">:</div>
    <div class="abs value" style="left: 206px; top: 254px;">{{ $shipment->total_koli }}</div>

    {{-- Total Berat --}}
    <div class="abs label" style="left: 24px; top: 289px;">Total Berat</div>
    <div class="abs" style="left: 190px; top: 289px;">:</div>
    <div class="abs value" style="left: 206px; top: 289px;">{{ $shipment->total_weight }} kg</div>

    {{-- Isi Barang --}}
    <div class="abs label" style="left: 24px; top: 324px;">Isi Barang</div>
    <div class="abs" style="left: 190px; top: 324px;">:</div>
    <div class="abs value maxw" style="left: 206px; top: 324px;">{{ $shipment->package_description ?? '-' }}</div>

    {{-- Tipe Pembayaran --}}
    <div class="abs label" style="left: 24px; top: 394px;">Tipe Pembayaran</div>
    <div class="abs" style="left: 190px; top: 394px;">:</div>
    <div class="abs" style="left: 206px; top: 391px;"><span
            class="badge">{{ strtoupper($shipment->payment_type ?? 'KREDIT') }}</span></div>

    {{-- Right column: PENGIRIM --}}
    <div class="abs section" style="left: 468px; top: 147px;">PENGIRIM</div>

    <div class="abs label" style="left: 472px; top: 182px;">Nama</div>
    <div class="abs" style="left: 553px; top: 181px;">:</div>
    <div class="abs value maxw-narrow" style="left: 565px; top: 181px;">{{ $shipment->sender_name }}</div>

    <div class="abs label" style="left: 472px; top: 211px;">Telepon</div>
    <div class="abs" style="left: 553px; top: 210px;">:</div>
    <div class="abs value maxw-narrow" style="left: 565px; top: 210px;">{{ $shipment->sender_phone }}</div>

    <div class="abs label" style="left: 472px; top: 240px;">Alamat</div>
    <div class="abs" style="left: 553px; top: 238px;">:</div>
    <div class="abs value maxw-narrow" style="left: 565px; top: 238px;">{{ $shipment->sender_address }}</div>

    {{-- Right column: PENERIMA --}}
    <div class="abs section" style="left: 468px; top: 280px;">PENERIMA</div>

    <div class="abs label" style="left: 472px; top: 315px;">Nama</div>
    <div class="abs" style="left: 553px; top: 314px;">:</div>
    <div class="abs value maxw-narrow" style="left: 565px; top: 314px;">{{ $shipment->receiver_name }}</div>

    <div class="abs label" style="left: 472px; top: 344px;">Telepon</div>
    <div class="abs" style="left: 553px; top: 343px;">:</div>
    <div class="abs value maxw-narrow" style="left: 565px; top: 343px;">{{ $shipment->receiver_phone }}</div>

    <div class="abs label" style="left: 472px; top: 373px;">Alamat</div>
    <div class="abs" style="left: 553px; top: 372px;">:</div>
    <div class="abs value maxw-narrow" style="left: 565px; top: 372px;">{{ $shipment->receiver_address }}</div>

    {{-- Footer tagline --}}
    <div class="abs tagline" style="left: 24px; top: 424px;">PARTNER LOGISTIK PROFESIONAL UNTUK BISNIS ANDA</div>
    <div class="abs small" style="left: 24px; top: 443px;">www.devankalogistik.com</div>

    {{-- Signature lines --}}
    <div class="abs sig" style="left: 548px; top: 435px;">PENERIMA</div>
    <div class="abs sig" style="left: 765px; top: 435px;">PETUGAS</div>

    {{-- Bottom note --}}
    <div class="abs small" style="left: 23px; top: 480px;">Simpan resi ini sebagai bukti pengiriman.</div>
</body>

</html>