<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Label {{ $shipment->resi_no }}</title>
    <style>
        @page {
            size: 4in 6in;
            margin: 0;
        }

        html,
        body {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 8pt;
            padding: 0.2cm;
            box-sizing: border-box;
        }

        /* dompdf TIDAK support flexbox/justify-content -> pakai absolute */
        .wrap {
            position: relative;
            border: 1px solid #000;
            box-sizing: border-box;
            height: 412pt;
            padding: 3px;
        }

        .koli-badge {
            text-align: center;
            font-weight: bold;
            font-size: 10pt;
            border-bottom: 1px solid #000;
            padding-bottom: 2px;
        }

        .company {
            text-align: center;
            font-size: 8pt;
            font-weight: bold;
        }

        .resi {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 3px 0;
        }

        .qr {
            text-align: center;
        }

        .route {
            text-align: center;
            font-size: 9pt;
            font-weight: bold;
        }

        .block {
            margin-left: 2px;
        }

        .lbl {
            font-weight: bold;
            font-size: 7pt;
            text-transform: uppercase;
            color: #555;
        }

        .name {
            font-weight: bold;
            font-size: 9pt;
        }

        .phone {
            font-size: 8pt;
        }

        .addr {
            font-size: 7.5pt;
            line-height: 1.2;
        }

        .box-table {
            width: 100%;
        }

        .box-table td {
            border: 1px solid #000;
            padding: 2px;
            text-align: center;
            width: 50%;
        }

        .box-table .k {
            font-size: 6.5pt;
            color: #555;
        }

        .box-table .v {
            font-size: 10pt;
            font-weight: bold;
        }

        .footer {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 14pt;
            text-align: center;
            font-size: 7pt;
            border-top: 1px solid #ccc;
            padding-top: 2px;
        }

        .tagline {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 3pt;
            text-align: center;
            font-size: 6.5pt;
            color: #555;
        }
    </style>
</head>

<body>
    @for($koli = 1; $koli <= $shipment->total_koli; $koli++)
        <div class="wrap">
            @if($logoUrl)
                <div style="text-align: center; margin-bottom: 8pt;">
                    <img src="{{ $logoUrl }}" alt="Logo" style="height: 25pt; width: auto;">
                </div>
            @endif
            <div class="koli-badge" style="margin-bottom: 8pt;">KOLI {{ $koli }} / {{ $shipment->total_koli }}</div>
            <div class="company" style="margin-bottom: 8pt;">PT. DEVANKA LOGISTIK INDONESIA</div>
            <div class="resi" style="margin-bottom: 8pt;">{{ $shipment->resi_no }}</div>
            <div class="qr" style="margin-bottom: 8pt;">
                <img src="{{ $barcodeBase64 }}" alt="Barcode" style="width: 100%; max-width: 180px; height: 38px;">
            </div>
            <div class="route" style="margin-bottom: 8pt;">{{ strtoupper($shipment->originBranch?->city ?? '') }} -
                {{ strtoupper($shipment->destinationBranch?->city ?? '') }}
            </div>

            <div class="block" style="margin-bottom: 8pt;">
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

            <div class="block" style="margin-bottom: 8pt;">
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

            <div class="block" style="margin-bottom: 8pt;">
                <div class="lbl">Isi Barang</div>
                <div>{{ $shipment->package_description ?? '-' }}</div>
            </div>

            <table class="box-table">
                <tr>
                    <td>
                        <div class="k">Qty/Koli</div>
                        <div class="v">{{ $shipment->total_koli }}</div>
                    </td>
                    <td>
                        <div class="k">Berat</div>
                        <div class="v">{{ $shipment->total_weight }} kg</div>
                    </td>
                </tr>
            </table>

            <div class="footer" style="display: none;">
                {{ $printSettings['footer_text'] ?? 'DEVANKA LOGISTIK INDONESIA - Simpan resi ini sebagai bukti pengiriman' }}
            </div>
        </div>
        @if($koli < $shipment->total_koli)
            <div style="page-break-after: always;"></div>
        @endif
    @endfor
</body>

</html>