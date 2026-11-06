<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->invoice_no }}</title>
    <style>
        @page { size: {{ ($printSettings['paper_format'] ?? 'A4') === 'thermal' ? '10cm 10cm' : (($printSettings['paper_format'] ?? 'A4') === 'dotmatrix-half' ? '241mm 140mm' : ($printSettings['paper_format'] ?? 'A4')) }}; margin: 1cm; }
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
            @if($logoUrl)
                <img src="{{ $logoUrl }}" style="max-height: 70px; max-width: 120px; margin-right: 8px;" alt="Logo">
            @endif
        </div>
        <div style="display: table-cell; vertical-align: middle; text-align: right;">
            <div style="font-size: 12pt; font-weight: bold;">{{ $printSettings['company_name'] ?? config('app.name') }}</div>
            @if(!empty($printSettings['header_text']))
                <small>{{ $printSettings['header_text'] }}</small>
            @endif
        </div>
    </div>
    <h3 style="text-align: center; margin: 0 0 12px 0;">INVOICE</h3>

    <table class="info" style="border: none;">
        <tr><td>No Invoice</td><td>: {{ $invoice->invoice_no }}</td><td>Tanggal Invoice</td><td>: {{ $invoice->invoice_date?->format('d/m/Y') ?? date('d/m/Y') }}</td></tr>
        <tr><td>Cabang</td><td>: {{ $invoice->branch?->name }}</td><td>Due Date</td><td>: {{ $invoice->due_date?->format('d/m/Y') ?? '-' }}</td></tr>
        <tr><td>Customer</td><td>: {{ $invoice->customer_name ?? '-' }}</td><td>Telepon</td><td>: {{ $invoice->customer_phone ?? '-' }}</td></tr>
        <tr><td>Alamat</td><td colspan="3">:
            {{ $invoice->customer_address ?? '-' }}
            @if($invoice->customer_village_code || $invoice->customer_district_code || $invoice->customer_regency_code || $invoice->customer_province_code)
                <br>
                {{ \App\Models\Region::where('code', $invoice->customer_village_code)->value('name') ?? '' }}
                {{ \App\Models\Region::where('code', $invoice->customer_district_code)->value('name') ?? '' }}
                {{ \App\Models\Region::where('code', $invoice->customer_regency_code)->value('name') ?? '' }}
                {{ \App\Models\Region::where('code', $invoice->customer_province_code)->value('name') ?? '' }}
            @endif
        </td></tr>
        <tr><td>Email</td><td>: {{ $invoice->customer_email ?? '-' }}</td><td>Status</td><td>: {{ $invoice->invoice_status }}</td></tr>
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
                <td>{{ $item->shipment?->originBranch?->city }} -&gt; {{ $item->shipment?->destinationBranch?->city }}</td>
                <td style="text-align: center;">{{ $item->shipment?->total_koli }}</td>
                <td style="text-align: center;">{{ $item->shipment?->total_weight }} kg</td>
                <td style="text-align: center;">{{ $item->shipment?->service_type }}</td>
                <td class="text-right">Rp {{ number_format(($item->shipment?->total_weight ?? 0) * (strtoupper($item->shipment?->service_type ?? '') === 'EXPRESS' ? (float)env('SHIPPING_RATE_EXPRESS', 15000) : (float)env('SHIPPING_RATE_REGULAR', 10000)), 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align: center;">Tidak ada data</td></tr>
            @endforelse
        </tbody>
    </table>

    <table style="width: 50%; margin-left: auto;">
        <tr><td>Subtotal</td><td class="text-right">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td></tr>
        <tr><td>Biaya Kirim</td><td class="text-right">Rp {{ number_format($invoice->shipping_cost, 0, ',', '.') }}</td></tr>
        <tr><td>Discount</td><td class="text-right">-Rp {{ number_format($invoice->discount, 0, ',', '.') }}</td></tr>
        <tr class="total grand"><td><strong>TOTAL</strong></td><td class="text-right"><strong>Rp {{ number_format($invoice->grandTotal(), 0, ',', '.') }}</strong></td></tr>
    </table>

    @if($invoice->notes)
        <div style="margin-top: 8px;"><strong>Catatan:</strong> {{ $invoice->notes }}</div>
    @endif

    <div class="footer">
        @if(!empty($printSettings['footer_text'])) {{ $printSettings['footer_text'] }} @else Terima kasih atas kepercayaan Anda. @endif
    </div>
</body>
</html>