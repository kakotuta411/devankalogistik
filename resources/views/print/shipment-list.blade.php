<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Shipment List</title>
    <style>
        @page { size: A4; margin: 1cm; }
        body { font-family: Arial, sans-serif; font-size: 9pt; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 4px; }
        th { background: #f0f0f0; }
        h2 { text-align: center; }
    </style>
</head>
<body>
    <h2>DAFTAR SHIPMENT</h2>
    <p>Generated: {{ $generated_at->format('d/m/Y H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Resi</th>
                <th>Asal</th>
                <th>Tujuan</th>
                <th>Koli</th>
                <th>Berat</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($shipments as $i => $s)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $s->resi_no }}</td>
                <td>{{ $s->originBranch?->city }}</td>
                <td>{{ $s->destinationBranch?->city }}</td>
                <td>{{ $s->total_koli }}</td>
                <td>{{ $s->total_weight }}</td>
                <td>{{ $s->shipment_status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
