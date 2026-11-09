<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase Order List</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 4px; }
        th { background-color: #f2f2f2; font-weight: bold; }
        h2 { margin-bottom: 10px; }
    </style>
</head>
<body>
    <h2>Daftar Purchase Order</h2>
    <p>Tanggal cetak: {{ $generated_at->format('d/m/Y H:i') }} | Total: {{ $pos->count() }} PO</p>
    <table>
        <thead>
            <tr>
                <th>No. PO</th>
                <th>Pelanggan</th>
                <th>Total</th>
                <th>Dibayar</th>
                <th>Sisa</th>
                <th>Status</th>
                <th>Dibuat</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pos as $po)
            <tr>
                <td>{{ $po->po_number }}</td>
                <td>{{ $po->customer?->company_name ?? $po->customer?->name ?? '-' }}</td>
                <td>{{ number_format($po->total_amount, 0, ',', '.') }}</td>
                <td>{{ number_format($po->paid_amount, 0, ',', '.') }}</td>
                <td>{{ number_format($po->getRemainingAmount(), 0, ',', '.') }}</td>
                <td>{{ ucfirst($po->status) }}</td>
                <td>{{ $po->created_at?->format('d/m/Y H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
