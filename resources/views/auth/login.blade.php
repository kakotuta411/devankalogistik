<!DOCTYPE html>
<html lang="id" data-bs-theme="auto">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bootstrap-icons.min.css') }}">
    <style>
        body { min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .sign-in-card { max-width: 400px; width: 100%; }
    </style>
</head>
<body class="bg-light">
    <form method="POST" action="/login" class="sign-in-card bg-white p-4 pb-5 m-3 shadow-sm rounded-3 text-center">
        @csrf
        <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" style="max-height: 120px; max-width: 100%; margin-bottom: 1rem;" onerror="this.style.display='none';">
        <h1 class="h3 mb-3 fw-normal">Please sign in</h1>

        @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <div class="form-floating mb-3">
            <input type="email" name="email" class="form-control" id="floatingInput" placeholder=" " required autofocus>
            <label for="floatingInput">Email address</label>
        </div>
        <div class="form-floating mb-3">
            <input type="password" name="password" class="form-control" id="floatingPassword" placeholder=" " required>
            <label for="floatingPassword">Password</label>
        </div>
        <div class="form-check text-start mb-4">
            <input class="form-check-input" type="checkbox" value="1" id="checkDefault" name="remember">
            <label class="form-check-label" for="checkDefault">Remember me</label>
        </div>
        <button class="btn btn-primary w-100 py-2" type="submit"><i class="bi bi-door-open me-2"></i>Sign in</button>
    </form>

    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
