# Laravel Security Hardening — Phase 2

> Code changes yang di-push via GitHub. Auto-deploy ke VPS.

---

## 1. Health Check Endpoint

**File**: `routes/api.php`

**Tujuan**: URL `/api/health` untuk UptimeRobot monitoring.

### Tambahkan
```php
use Illuminate\Support\Facades\DB;

Route::get('/health', function () {
    try {
        $db = DB::connection()->getPdo() ? 'connected' : 'disconnected';
    } catch (\Exception $e) {
        $db = 'disconnected';
    }
    return response()->json([
        'status' => 'ok',
        'db' => $db,
        'time' => now()->toIso8601String(),
    ]);
});
```

### Verifikasi
```bash
curl https://devakalogistik.web.id/api/health
# {"status":"ok","db":"connected","time":"2026-11-08T10:00:00+07:00"}
```

---

## 2. Rate Limiting

**File**: `routes/web.php` atau `routes/api.php`

**Tujuan**: Batasi request per IP untuk cegah brute force & abuse.

### Tambahkan di RouteServiceProvider atau routes
```php
// Di routes/api.php
Route::middleware('throttle:60,1')->group(function () {
    // API routes yang perlu rate limit
});

// Atau di RouteServiceProvider
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});
```

### Rate Limit Spesifik
```php
// Login: max 5 attempts per minute
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1');

// Public tracking: max 30 per minute
Route::get('/api/tracking/{resi}', [TrackingController::class, 'show'])
    ->middleware('throttle:30,1');

// API customers: max 20 per minute
Route::get('/api/customers/search', [CustomerController::class, 'search'])
    ->middleware('throttle:20,1');
```

---

## 3. Nginx Security Headers (via Laravel Middleware)

**Alternatif**: Jika tidak mau edit Nginx config, bisa pakai middleware Laravel.

### Buat Middleware
```bash
php artisan make:middleware SecurityHeaders
```

### Isi Middleware
```php
// app/Http/Middleware/SecurityHeaders.php
public function handle(Request $request, Closure $next): Response
{
    $response = $next($request);
    $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
    $response->headers->set('X-Content-Type-Options', 'nosniff');
    $response->headers->set('X-XSS-Protection', '1; mode=block');
    $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
    return $response;
}
```

### Register di bootstrap/app.php
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
})
```

**Catatan**: Lebih baik pakai Nginx headers (Phase 1) karena lebih cepat. Middleware ini sebagai fallback.

---

## 4. CORS Configuration

**File**: `config/cors.php`

**Tujuan**: Izinkan domain tertentu akses API (public tracking).

### Config
```php
'paths' => ['api/*'],
'allowed_methods' => ['*'],
'allowed_origins' => ['*'],  // Atau spesifik: ['https://wordpress-site.com']
'allowed_origins_patterns' => [],
'allowed_headers' => ['*'],
'exposed_headers' => [],
'max_age' => 0,
'supports_credentials' => false,
```

**Catatan**: Untuk production, lebih aman set `allowed_origins` ke domain WordPress spesifik, bukan `*`.

---

## 5. Input Validation & Sanitization

### Phone Input (digit-only)
Sudah diterapkan di views. Pastikan di controller juga:
```php
$validated = $request->validate([
    'sender_phone' => 'required|digits_between:10,15',
    'receiver_phone' => 'required|digits_between:10,15',
]);
```

### XSS Prevention
- Blade `{{ }}` auto-escape (sudah default)
- Jangan pakai `{!! !!}` untuk user input
- Sanitize HTML input jika ada rich text

### SQL Injection Prevention
- Eloquent ORM (sudah dipakai) → parameterized queries
- Jangan pakai `DB::raw()` dengan user input
- Jangan concatenate SQL string

---

## 6. Session Security

### Config (config/session.php)
```php
'secure' => env('SESSION_SECURE_COOKIE', true),  // HTTPS only
'http_only' => true,                              // JS tidak bisa akses cookie
'same_site' => 'lax',                            // CSRF protection
'lifetime' => 120,                               // 2 jam
```

### .env
```
SESSION_SECURE_COOKIE=true
```

---

## 7. File Upload Security

### Validasi File Upload (jika ada)
```php
$request->validate([
    'logo' => 'required|image|mimes:png,jpg,jpeg,svg|max:2048',  // max 2MB
    'pod_photo' => 'image|mimes:png,jpg,jpeg|max:5120',          // max 5MB
]);
```

### Storage Security
- Upload ke `storage/app/public/` (bukan `public/`)
- Serve via `storage:link` (symlink)
- Validasi MIME type (bukan hanya extension)
- Rename file dengan UUID (jangan pakai nama asli user)

---

## 8. Database Security

### .env (jangan hardcode credentials)
```env
DB_PASSWORD=***  # jangan commit ke git
```

### Least Privilege DB User
```sql
-- Jangan pakai root untuk app
CREATE USER 'devanka'@'localhost' IDENTIFIED BY 'strong_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON devanka_logistik.* TO 'devanka'@'localhost';
-- Jangan GRANT ALL (kecuali migrate)
FLUSH PRIVILEGES;
```

### Backup Encryption (optional)
```bash
# Encrypt backup file
gpg --symmetric --cipher-algo AES256 db_backup.sql.gz
# Decrypt
gpg --decrypt db_backup.sql.gz.gpg > db_backup.sql.gz
```

---

## 9. Error Handling

### APP_DEBUG=false (sudah)
- Jangan tampilkan stack trace ke user
- Log error ke `storage/logs/laravel.log`
- Tampilkan generic error page

### Custom Error Pages
Buat di `resources/views/errors/`:
- `404.blade.php` — Page not found
- `500.blade.php` — Server error
- `403.blade.php` — Forbidden

---

## 10. Audit Trail

**Sudah ada**: `audit_logs` table mencatat semua CRUD operations.

### Pastikan Audit Log mencatat:
- actor_id (user yang melakukan)
- actor_role (role user)
- action (create/update/delete)
- action_type (shipment/invoice/etc)
- ip_address
- user_agent
- details (JSON)
- created_at

### Review Audit Log
```sql
SELECT * FROM audit_logs ORDER BY created_at DESC LIMIT 20;
```

---

## Checklist Implementasi Phase 2

- [ ] Health check endpoint (`/api/health`)
- [ ] Rate limiting (login, tracking, API)
- [ ] Security headers (Nginx atau middleware)
- [ ] CORS config (spesifik domain WordPress)
- [ ] Input validation (phone digit-only, dll)
- [ ] Session security (secure, http_only, same_site)
- [ ] File upload validation (jika ada)
- [ ] DB user least privilege
- [ ] Custom error pages (404, 500, 403)
- [ ] Audit trail verified

## Catatan
- Semua perubahan ini di-push via GitHub
- Auto-deploy ke VPS setelah push
- Test di local (Docker) dulu sebelum push
- Review PR sebelum merge ke master