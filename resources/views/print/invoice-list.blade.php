<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice List</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 4px; }
        th { background-color: #f2f2f2; font-weight: bold; }
        h2 { margin-bottom: 10px; }
    </style>
</head>
<body>
    <h2>Daftar Invoice</h2>
    <p>Tanggal cetak: {{ $generated_at->format('d/m/Y H:i') }} | Total: {{ $invoices->count() }} invoice</p>
    <table>
        <thead>
            <tr>
                <th>No. Invoice</th>
                <th>Pelanggan</th>
                <th>Cabang</th>
                <th>Status</th>
                <th>Subtotal</th>
                <th>Dibuat</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoices as $inv)
            <tr>
                <td>{{ $inv->invoice_no }}</td>
                <td>{{ $inv->customer_name }}</td>
                <td>{{ $inv->branch?->city ?? '-' }}</td>
                <td>{{ $inv->invoice_status }}</td>
                <td>{{ number_format($inv->subtotal, 0, ',', '.') }}</td>
                <td>{{ $inv->created_at?->format('d/m/Y H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
