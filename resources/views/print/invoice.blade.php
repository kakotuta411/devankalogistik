<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->invoice_no }}</title>
    <style>
        @page {
            size:
                {{ ($printSettings['paper_format'] ?? 'A4') === 'thermal' ? '10cm 10cm' : (($printSettings['paper_format'] ?? 'A4') === 'dotmatrix-half' ? '241mm 140mm' : ($printSettings['paper_format'] ?? 'A4')) }}
            ;
            margin: 1cm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            padding-bottom: 50px;
        }

        .header {
            margin-bottom: 14px;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px 6px;
        }

        th {
            background: #f0f0f0;
            text-align: center;
        }

        .info td {
            padding: 3px 6px;
            vertical-align: top;
        }

        .info td:first-child {
            font-weight: bold;
            width: 120px;
        }

        .total td {
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .grand {
            font-size: 11pt;
        }

        .footer {
            position: fixed;
            bottom: 10px;
            left: 28px;
            right: 28px;
            text-align: center;
            font-size: 9pt;
        }
    </style>
</head>

<body>
    <div class="header" style="display: table; width: 100%;">
        <div style="display: table-cell; width: 80px; vertical-align: middle;">
            @if($logoUrl)
                <img src="{{ $logoUrl }}" style="max-height: 70px; max-width: 120px; margin-right: 8px;" alt="Logo">
            @endif
        </div>
        <div style="display: table-cell; vertical-align: middle; text-align: right;">
            <div style="font-size: 12pt; font-weight: bold;">{{ $printSettings['company_name'] ?? config('app.name') }}
            </div>
            @if(!empty($printSettings['header_text']))
                <small>{{ $printSettings['header_text'] }}</small>
            @endif
        </div>
    </div>
    <h3 style="text-align: center; margin: 0 0 12px 0;">INVOICE</h3>

    <table class="info" style="border: none;">
        <tr>
            <td>No Invoice</td>
            <td>: {{ $invoice->invoice_no }}</td>
            <td>Tanggal Invoice</td>
            <td>: {{ $invoice->invoice_date?->format('d/m/Y') ?? date('d/m/Y') }}</td>
        </tr>
        <tr>
            <td>Cabang</td>
            <td>: {{ $invoice->branch?->name }}</td>
            <td>Due Date</td>
            <td>: {{ $invoice->due_date?->format('d/m/Y') ?? '-' }}</td>
        </tr>
        <tr>
            <td>Customer</td>
            <td>: {{ $invoice->customer_name ?? '-' }}</td>
            <td>Telepon</td>
            <td>: {{ $invoice->customer_phone ?? '-' }}</td>
        </tr>
        <tr>
            <td>Alamat</td>
            <td colspan="3">:
                {{ $invoice->customer_address ?? '-' }}
                @if($invoice->customer_village_code || $invoice->customer_district_code || $invoice->customer_regency_code || $invoice->customer_province_code)
                    <br>
                    {{ \App\Models\Region::where('code', $invoice->customer_village_code)->value('name') ?? '' }}
                    {{ \App\Models\Region::where('code', $invoice->customer_district_code)->value('name') ?? '' }}
                    {{ \App\Models\Region::where('code', $invoice->customer_regency_code)->value('name') ?? '' }}
                    {{ \App\Models\Region::where('code', $invoice->customer_province_code)->value('name') ?? '' }}
                @endif
            </td>
        </tr>
        <tr>
            <td>Email</td>
            <td>: {{ $invoice->customer_email ?? '-' }}</td>
            <td>Status</td>
            <td>: {{ $invoice->invoice_status }}</td>
        </tr>
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
            @forelse($invoice->items as $i => $item)
                <tr>
                    <td style="text-align: center;">{{ $i + 1 }}</td>
                    <td>{{ $item->shipment?->resi_no }}</td>
                    <td>{{ $item->shipment?->originBranch?->city }} -&gt; {{ $item->shipment?->destinationBranch?->city }}
                    </td>
                    <td style="text-align: center;">{{ $item->shipment?->total_koli }}</td>
                    <td style="text-align: center;">{{ $item->shipment?->total_weight }} kg</td>
                    <td style="text-align: center;">{{ $item->shipment?->service_type }}</td>
                    <td class="text-right">Rp
                     {{ number_format(($item->shipment?->total_weight ?? 0) * $invoice->shipping_cost, 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center;">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Payment info (left) + Price detail (right) -->
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <!-- Payment info -->
            <td style="width: 40%;  vertical-align: top; border: none; padding: 0;">
                <table style="width: 100%; border: none; font-size: 8.5pt;">
                    <tr>
                        <td style="width: 20%; font-weight: bold; text-align: left; border: none; padding: 1px 5px;">Bank</td>
                        <td style="text-align: left; border: none; padding: 1px 4px;">BCA</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; text-align: left; border: none; padding: 1px 4px;">Nama</td>
                        <td style="text-align: left; border: none; padding: 1px 4px;">PT. DEVANKA LOGISTIK INDONESIA
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; text-align: left; border: none; padding: 1px 4px;">No. Rek.</td>
                        <td style="text-align: left; border: none; padding: 1px 4px;">645.070.1996 </td>
                    </tr>
                </table>
            </td>
            <!-- Price detail -->
            <td style="width: 70%; vertical-align: top; border: none; padding: 0;">
                @if($invoice->isChild())
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="font-weight: bold; border: 1px solid #000; padding: 5px 6px;">Sisa dari invoice
                                {{ $invoice->parentInvoice?->invoice_no }}</td>
                            <td style="text-align: right; border: 1px solid #000; padding: 5px 6px;">Rp
                                {{ number_format($invoice->displayTotal(), 0, ',', '.') }}</td>
                        </tr>
                    </table>
                @else
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="font-weight: bold; border: 1px solid #000; padding: 5px 6px;">Biaya Pengiriman
                                ({{ number_format($invoice->shipping_cost, 0, ',', '.') }}/kg x
                                {{ number_format($invoice->total_weight, 2, ',', '.') }} kg)</td>
                            <td class="text-right" style="border: 1px solid #000; padding: 5px 6px;">Rp
                                {{ number_format($invoice->shipping_cost * $invoice->total_weight, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; border: 1px solid #000; padding: 5px 6px;">Biaya Packaging</td>
                            <td class="text-right" style="border: 1px solid #000; padding: 5px 6px;">Rp
                                {{ number_format($invoice->packaging_cost, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; border: 1px solid #000; padding: 5px 6px;">Charge Alat</td>
                            <td class="text-right" style="border: 1px solid #000; padding: 5px 6px;">Rp
                                {{ number_format($invoice->equipment_charge, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; border: 1px solid #000; padding: 5px 6px;">Biaya Lain-lain</td>
                            <td class="text-right" style="border: 1px solid #000; padding: 5px 6px;">Rp
                                {{ number_format($invoice->other_costs, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; border: 1px solid #000; padding: 5px 6px;">Diskon
                                ({{ number_format($invoice->discount_value, 2, ',', '.') }}%)</td>
                            <td class="text-right" style="border: 1px solid #000; padding: 5px 6px;">-Rp
                                {{ number_format($invoice->discount, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; border: 1px solid #000; padding: 5px 6px;">PPFTZ 03</td>
                            <td class="text-right" style="border: 1px solid #000; padding: 5px 6px;">Rp
                                {{ number_format($invoice->ppftz03, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; border: 1px solid #000; padding: 5px 6px;">PPN
                                ({{ number_format($invoice->ppn, 2, ',', '.') }}%)</td>
                            <td class="text-right" style="border: 1px solid #000; padding: 5px 6px;">Rp
                                {{ number_format($invoice->ppnAmount(), 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; border: 1px solid #000; padding: 5px 6px;">PPH
                                ({{ number_format($invoice->pph, 2, ',', '.') }}%)</td>
                            <td class="text-right" style="border: 1px solid #000; padding: 5px 6px;">Rp
                                {{ number_format($invoice->pphAmount(), 0, ',', '.') }}</td>
                        </tr>
                        <tr class="total grand">
                            <td style="font-weight: bold; border: 1px solid #000; padding: 5px 6px;">TOTAL</td>
                            <td class="text-right" style="font-weight: bold; border: 1px solid #000; padding: 5px 6px;">Rp
                                {{ number_format($invoice->displayTotal(), 0, ',', '.') }}</td>
                        </tr>
                    </table>
                @endif
            </td>
        </tr>
    </table>

    @if($invoice->notes)
        <div style="margin-top: 8px;"><strong>Catatan:</strong> {{ $invoice->notes }}</div>
    @endif

    <div class="footer">
        <!-- @if(!empty($printSettings['footer_text'])) {{ $printSettings['footer_text'] }} @else Terima kasih atas kepercayaan Anda. 
        @endif -->
        <div style="margin-top: 8px;"><strong> PT. Devanka Logistik Indonesia - Simpan dokumen ini sebagai bukti pembayaran</strong></div>
    </div>
</body>

</html>