# Logistik Tracking (Minimal) — System Operations & API Manual

**Document ID:** LT-MIN-RUNBOOK-001
**Project:** akllogistik-minimal
**Status:** Draft

> ⚠️ **EXCLUDED** — Semua endpoint `/api/cases/*`, `/api/decisions/*`, `/api/evidence/*`, SLA engine, Filament admin routes. Lihat project lama `LT-RUNBOOK-001`.

---

## 1. Quick Start & Setup Infrastruktur

### 1.1 Menjalankan Container (Docker)

```bash
cd /home/ambon/akllogistik-minimal
docker compose up -d        # postgres, redis, minio, app, nginx (NO worker)
docker compose exec app php artisan migrate:fresh --seed
docker compose exec app php artisan regions:sync
```

### 1.2 Konfigurasi Environment (.env)

```env
APP_NAME="Logistik Tracking"
APP_ENV=local
APP_KEY=base64:generated
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=logistik_min
DB_USERNAME=[REDACTED]
DB_PASSWORD=[REDACTED]

REDIS_HOST=redis
CACHE_STORE=redis
SESSION_DRIVER=file
QUEUE_CONNECTION=sync  # No queue worker in minimal

AWS_ACCESS_KEY_ID=[REDACTED]
AWS_SECRET_ACCESS_KEY=[REDACTED]
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=logistik-tracking
AWS_ENDPOINT=http://minio:9000
AWS_USE_PATH_STYLE_ENDPOINT=true
```

### 1.3 Menjalankan Migrasi Database

```bash
docker compose exec app php artisan migrate --force
docker compose exec app php artisan db:seed  # seeds users, branches, regions
```

> ⚠️ **EXCLUDED:** Tidak ada `php artisan queue:work` — tidak ada Redis queue worker di project minimal.

## 2. Standar Header & Autentikasi

### 2.1 Header Wajib untuk Seluruh Endpoint Internal

```
Authorization: Bearer {token}
Accept: application/json
Content-Type: application/json
```

### 2.2 Auth Flow

```
POST /api/auth/login
Body: { "email": "user@domain.com", "password": "[REDACTED]" }
→ Response: { "token": "xxxxxx", "user": {...}, "role": "Admin" }

POST /api/auth/logout
→ Revokes token
```

## 3. Katalog Lengkap RESTful API Endpoints

### 3.1 Domain Keamanan & Autentikasi Staf (`/api/auth`)

```
POST   /api/auth/login     → login, return token
POST   /api/auth/logout    → revoke current token
GET    /api/auth/me        → current user info
```

### 3.2 Domain Pengiriman & Koli Fisik (`/api/shipments`)

```
GET    /api/shipments                → list (branch-scoped, query: status, date)
POST   /api/shipments                → create (Staf+)
GET    /api/shipments/{id}           → detail (cross-branch)
PUT    /api/shipments/{id}           → update (Staf+)
PUT    /api/shipments/{id}/adjust-koli → adjust koli (Staf+, pre-departure)
POST   /api/shipments/{id}/pod       → upload POD (Staf+, MinIO)
```

### 3.3 Domain Manifest Keberangkatan Armada (`/api/manifests`)

```
GET    /api/manifests                     → list (branch-scoped)
POST   /api/manifests                     → create draft (Staf+)
GET    /api/manifests/{id}                → detail (cross-branch)
GET    /api/manifests/{id}/shipments      → assigned shipments
PUT    /api/manifests/{id}/assign          → assign shipments (origin branch)
PUT    /api/manifests/{id}/dispatch        → dispatch (origin branch only)
PUT    /api/manifests/{id}/receive         → receive (destination branch only)
POST   /api/manifests/{id}/complete        → complete (origin after receive)
```

> ⚠️ **EXCLUDED:** `/api/cases/*` — lihat project lama §3.5.

### 3.4 Domain Pelacakan Publik (Customer-Facing Zero PII)

```
GET  /api/tracking/{resi}      → public, zero PII, tracking timeline
POST /api/tracking/search      → public, partial resi search
```

### 3.5 Domain Region / Wilayah

```
GET /api/regions/provinces
GET /api/regions/regencies?province={code}
GET /api/regions/districts?regency={code}
GET /api/regions/villages?district={code}
```

### 3.6 Domain Print / Template

```
GET /print/shipments/{id}/label     → resi label PDF (thermal 10x10cm)
GET /print/manifests/{id}/sheet     → manifest sheet PDF (A4)
GET /print/shipments/list           → shipment list PDF (branch filter)

GET /admin/template-management     → logo upload + print settings (Admin)
POST /admin/template-management   → save settings (Admin)
```

## 4. Alur Uji Coba Cepat (End-to-End Simulation)

```
1. Login as admin@logistik.id → get token
2. Create shipment (TRK-xxxxx) → Staf Jakarta
3. Dispatch via manifest (MNF-xxxxx) → Staf Surabaya
2. Receive manifest → Staf Jakarta
5. Upload POD → Staf Jakarta
6. Check tracking → GET /api/tracking/{resi} (public)
```

## 5. Panduan Deploy Produksi ke VPS Klien

### 5.1 Urutan Perintah Eksekusi di VPS

```bash
git clone {repo}
cd akllogistik-minimal
cp .env.example .env
# edit .env (DB, MINIO, APP_KEY)
docker compose up -d --build
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --force
docker compose exec app php artisan regions:sync
docker compose exec app php artisan queue:clear redis
```

> ⚠️ **EXCLUDED:** Tidak ada queue worker setup. Tidak ada SLA engine daemon.

### 5.2 Akses URL Utama

- **Admin panel:** http://localhost:8000/admin (Blade login)
- **API base:** http://localhost:8000/api
- **Public tracking:** http://localhost:8000/api/tracking/{resi}

### 5.3 Akun Uji Coba

```
admin@logistik.id        / [REDACTED]  → Admin (full)
kacab.cgk@logistik.id    / [REDACTED]  → Head_of_Branch (CGK)
staf.cgk@logistik.id     / [REDACTED]  → Staf (CGK)
kacab.sub@logistik.id    / [REDACTED]  → Head_of_Branch (SUB)
staf.sub@logistik.id     / [REDACTED]  → Staf (SUB)
direksi@logistik.id      / [REDACTED]  → Executive_Viewer (read-only)
```

> ⚠️ **EXCLUDED:** Tidak ada akun dengan role kasus/leader-spesifik untuk Case Management.

---

**End of document — LT-MIN-RUNBOOK-001 v1.0-draft**
