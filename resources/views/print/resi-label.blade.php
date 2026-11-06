<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Resi {{ $shipment->resi_no }}</title>
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
    @for($koli = 1; $koli <= $shipment->total_koli; $koli++)
    <div class="wrap">
        <div style="text-align: center; font-weight: bold; font-size: 11pt; margin-bottom: 4px;">KOLI {{ $koli }} / {{ $shipment->total_koli }}</div>
        <div class="top">
            @if($logoUrl)
                <img src="{{ $logoUrl }}" style="max-height: 36px; max-width: 100%; margin-bottom: 6px;" alt="Logo"><br>
            @endif
            <!-- <strong>{{ $printSettings['company_name'] ?? config('app.name') }}</strong><br>
            <span class="meta">{{ $shipment->created_at?->format('d M Y, H:i') }}</span><br> -->
            <span
                class="svc">{{ strtoupper($shipment->service_type ?? 'REGULER ') }}{{ $shipment->created_at?->format(' d M Y, H:i') }}</span>
        </div>
        <div class="resi">{{ $shipment->resi_no }}</div>
        <div class="qr">
            <img src="{{ $qrBase64 }}" alt="QR" style="width: 70px; height: 70px;">
        </div>
        <div class="route">{{ strtoupper($shipment->originBranch?->city ?? '') }} -
            {{ strtoupper($shipment->destinationBranch?->city ?? '') }}
        </div>

        <div class="block">
            <div class="lbl">Penerima</div>
            <div class="name">{{ $shipment->receiver_name }}</div>
            <div class="phone">{{ $shipment->receiver_phone }}</div>
            <div class="addr">
                {{ $shipment->receiver_address }}
                @if($shipment->receiver_village_code || $shipment->receiver_district_code || $shipment->receiver_regency_code || $shipment->receiver_province_code)
                    <br>
                    {{ strtolower(\App\Models\Region::where('code', $shipment->receiver_province_code)->value('name') ?? '') }},
                    {{ strtolower(\App\Models\Region::where('code', $shipment->receiver_regency_code)->value('name') ?? '') }},
                    {{ strtolower(\App\Models\Region::where('code', $shipment->receiver_village_code)->value('name') ?? '') }}
                @endif
            </div>
        </div>

        <div class="block">
            <div class="lbl">Pengirim</div>
            <div class="name">{{ $shipment->sender_name }}</div>
            <div class="phone">{{ $shipment->sender_phone }}</div>
            <div class="addr">
                {{ $shipment->sender_address }}
                @if($shipment->sender_village_code || $shipment->sender_district_code || $shipment->sender_regency_code || $shipment->sender_province_code)
                    <br>
                    {{ strtolower(\App\Models\Region::where('code', $shipment->sender_province_code)->value('name') ?? '') }},
                    {{ strtolower(\App\Models\Region::where('code', $shipment->sender_regency_code)->value('name') ?? '') }},
                    {{ strtolower(\App\Models\Region::where('code', $shipment->sender_village_code)->value('name') ?? '') }}
                @endif
            </div>
        </div>

        <div class="block">
            <div class="lbl">Isi Barang</div>
            <div>{{ $shipment->package_description ?? '-' }}</div>
        </div>

        <div class="row">
            <div class="box">
                <div class="k">Qty/Koli</div>
                <div class="v">{{ $shipment->total_koli }}</div>
            </div>
            <div class="box">
                <div class="k">Berat</div>
                <div class="v">{{ $shipment->total_weight }} kg</div>
            </div>
        </div>

        <div class="footer">
            {{ $printSettings['footer_text'] ?? 'AKL LOGISTIK - Simpan resi ini sebagai bukti pengiriman' }}
        </div>
    </div>
    @if($koli < $shipment->total_koli)
    <div style="page-break-after: always;"></div>
    @endif
    @endfor
</body>

</html>