<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 - Akses Ditolak</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container text-center py-5">
        <h1 class="display-1 fw-bold text-warning">403</h1>
        <h2 class="mb-3">Akses Ditolak</h2>
        <p class="text-muted mb-4">Anda tidak memiliki izin untuk mengakses halaman ini.</p>
        <a href="{{ url('/') }}" class="btn btn-primary">Kembali ke Dashboard</a>
    </div>
</body>
</html>