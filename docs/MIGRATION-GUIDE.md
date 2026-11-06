# Akl Logistik — Shared Hosting Migration Guide

**Project:** akl-logistik-hosting
**Source:** akllogistik-minimal (PostgreSQL + Redis + MinIO)
**Target:** MariaDB + File Cache + Local Disk Storage
**Date:** 2026-10-31

---

## 1. Tujuan Migrasi

Mengadaptasi project `akllogistik-minimal` (yang awalnya dirancang untuk VPS dengan Docker full stack) menjadi versi yang kompatibel dengan **shared hosting** (Hostinger Web App Hosting, Niagahoster, DomaiNesia, dll.).

Shared hosting biasanya hanya menyediakan:
- PHP (8.2+)
- MySQL/MariaDB
- Apache/Nginx (managed)
- Tidak ada Docker, tidak ada root access, tidak ada PostgreSQL, tidak ada Redis

## 2. Perubahan yang Dilakukan

### 2.1 Database: PostgreSQL → MariaDB 11

| Item | Sebelum (PostgreSQL) | Sesudah (MariaDB) |
|------|---------------------|-------------------|
| Connection | `pgsql` | `mysql` |
| Port | 5433 | 3306 |
| Host | `127.0.0.1` | `mysql` (Docker) / `127.0.0.1` (hosting) |
| Driver | `pdo_pgsql` | `pdo_mysql` |

**Migration fixes:**
- `jsonb` → `json` (audit_logs.details)
- `->check()` constraints removed (MySQL doesn't support CHECK in older versions; Laravel Blueprint `check()` generates raw SQL that may fail)
- Duplicate `seal_number` column removed from manifests migration
- `enum` type retained (supported in both PG and MySQL)

### 2.2 Cache: Redis → File

| Item | Sebelum | Sesudah |
|------|---------|---------|
| `CACHE_STORE` | `redis` | `file` |
| Redis config | Present | Removed |
| Cache path | Redis server | `storage/framework/cache/data` |

### 2.3 Storage: MinIO/S3 → Local Disk

| Item | Sebelum | Sesudah |
|------|---------|---------|
| `FILESYSTEM_DISK` | `local` | `public` |
| AWS/S3 config | Present | Removed |
| POD uploads | MinIO (S3-compatible) | `storage/app/public/pods/` |
| Logo uploads | MinIO (S3-compatible) | `storage/app/public/logos/` |

**Catatan:** Run `php artisan storage:link` untuk membuat symlink `public/storage` → `storage/app/public`.

### 2.4 Docker: Full Stack → MySQL + PHP-FPM + Nginx

| Service | Sebelum | Sesudah |
|---------|---------|---------|
| PostgreSQL | ✅ | ❌ (diganti MariaDB) |
| Redis | ✅ | ❌ (diganti file cache) |
| MinIO | ✅ | ❌ (diganti local disk) |
| PHP-FPM | ✅ | ✅ |
| Nginx | ✅ | ✅ |
| MariaDB | ❌ | ✅ (baru) |

### 2.5 Queue: Tidak Berubah

`QUEUE_CONNECTION=sync` — tidak butuh worker process. Semua proses synchronous.

## 3. File yang Diubah

| File | Perubahan |
|------|-----------|
| `.env` | DB_CONNECTION=mysql, CACHE_STORE=file, FILESYSTEM_DISK=public, hapus Redis/AWS config |
| `.env.example` | Template untuk production (shared hosting) |
| `docker-compose.yml` | MariaDB + PHP-FPM + Nginx (tanpa PG/Redis/MinIO) |
| `Dockerfile` | pdo_mysql (bukan pdo_pgsql), hapus redis extension, tambah storage dirs |
| `docker/nginx/default.conf` | Port 80, fastcgi_pass app:9000 |
| `docker/php-fpm.conf` | Listen 9000 (untuk cross-container) |
| `database/migrations/0001_01_01_000000_create_users_table.php` | Hapus CHECK constraint |
| `database/migrations/2026_10_28_072617_create_manifests_table.php` | Hapus duplicate seal_number, hapus CHECK constraint |
| `database/migrations/2026_10_28_072620_create_audit_logs_table.php` | jsonb → json, hapus CHECK constraint |

## 4. Cara Deploy ke Shared Hosting

### 4.1 Persiapan
1. Upload semua file ke hosting (via FTP/Git deploy)
2. Pastikan PHP 8.3+ tersedia
3. Buat database MySQL di cPanel/hosting panel
4. Update `.env` dengan kredensial database hosting

### 4.2 Instalasi
```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 4.3 Konfigurasi Web Server
- Document root: `/public`
- Pastikan `mod_rewrite` aktif (Apache) atau nginx config sesuai

### 4.4 Environment Variables (.env)
```env
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=mysql
DB_HOST=127.0.0.1  # atau host dari hosting
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=username_db
DB_PASSWORD=password_db
CACHE_STORE=file
FILESYSTEM_DISK=public
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
```

## 5. Perbedaan dengan Versi VPS

| Fitur | Versi VPS (akllogistik-minimal) | Versi Hosting (akl-logistik-hosting) |
|-------|-------------------------------|--------------------------------------|
| Database | PostgreSQL 15 | MariaDB 11 |
| Cache | Redis 7 | File |
| Storage | MinIO (S3) | Local disk |
| Queue | Sync (no worker) | Sync (no worker) |
| Docker | Full stack | MySQL + PHP-FPM + Nginx (dev only) |
| Upload POD | MinIO | Local disk (storage/app/public/pods) |
| Upload Logo | MinIO | Local disk (storage/app/public/logos) |

## 6. Catatan Penting

- **POD upload** (foto bukti pengiriman) di versi hosting disimpan di local disk. Pastikan hosting menyediakan cukup storage.
- **Logo upload** di template management juga disimpan di local disk.
- **Region sync** (artisan regions:sync) tetap berjalan via cron job di hosting (jika hosting support cron).
- **QR code** (chillerlan/php-qrcode) tetap berfungsi (local, offline).
- **domPDF** tetap berfungsi (logo di-convert ke base64 untuk offline rendering).
