<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Manifest List</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 4px; }
        th { background-color: #f2f2f2; font-weight: bold; }
        h2 { margin-bottom: 10px; }
    </style>
</head>
<body>
    <h2>Daftar Manifest</h2>
    <p>Tanggal cetak: {{ $generated_at->format('d/m/Y H:i') }} | Total: {{ $manifests->count() }} manifest</p>
    <table>
        <thead>
            <tr>
                <th>No. Manifest</th>
                <th>Asal</th>
                <th>Tujuan</th>
                <th>Status</th>
                <th>No. Kendaraan</th>
                <th>Driver</th>
                <th>Dibuat</th>
            </tr>
        </thead>
        <tbody>
            @foreach($manifests as $m)
            <tr>
                <td>{{ $m->manifest_no }}</td>
                <td>{{ $m->originBranch?->city ?? '-' }}</td>
                <td>{{ $m->destinationBranch?->city ?? '-' }}</td>
                <td>{{ $m->manifest_status }}</td>
                <td>{{ $m->vehicle_no ?? '-' }}</td>
                <td>{{ $m->driver_name ?? '-' }}</td>
                <td>{{ $m->created_at?->format('d/m/Y H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
