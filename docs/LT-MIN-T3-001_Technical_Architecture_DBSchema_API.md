# Logistik Tracking (Minimal) — Technical Architecture

**Document ID:** LT-MIN-T3-001
**Project:** akllogistik-minimal
**Framework:** Laravel 11, PHP 8.3
**Database:** PostgreSQL 15
**UI:** Laravel Blade
**Status:** Draft — Ready for implementation

> ⚠️ **EXCLUDED** — Case Management, Filament, Redis SLA Engine. Lihat project lama `LT-T3-001` untuk full technical spec.

---

## 1. Posisi dan Ruang Lingkup Tahap 3

Versi minimal dari technical architecture. Fokus: DB schema, API, security, branch scoping. Case Management tables dan Filament-specific architecture dihapus.

## 2. Acuan Baku dari Tahap 1 & Tahap 2

Mengikuti struktur dan keputusan dari project lama:
- Shipment format: `TRK-{YYYYMMDD}-{SEQ:5}`
- Manifest format: `MNF-{YYYYMMDD}-{SEQ:5}`
- Seal number: `SEAL-{manifest_no}` auto-generate
- User UUID primary key
- BranchScope untuk data isolation

> ⚠️ **EXCLUDED:** Case statuses, Case categories, SLA engine, Redis tracking event timing — tidak berlaku di project minimal.

## 3. Tech Stack & Desain Arsitektur Teknis

### 3.1 Komponen & Pilihan Teknologi

| Komponikomponen | Teknologi | Notes |
|---|---|---|
| Backend | Laravel 11, PHP 8.3 | Full Blade |
| Database | PostgreSQL 15 | UUID primary keys |
| Cache | Redis 7 | Cache only, no queue/SLA engine |
| Storage | MinIO S3 | POD images, logo, region dump |
| Docker | Docker Compose v2 | php-fpm + nginx + postgres + redis + minio |
| PDF | barryvdh/laravel-dompdf | Print templates |
| Image | intervention/image | QR code generation |
| Auth | Sanctum (API token) | Session for web auth |

> ⚠️ **EXCLUDED dari stack:** Filament v3, Filament Resources/Pages/Widgets, Redis queue worker, Case Management models.

### 3.2 Titik Konfigurasi Multi-Klien (Zero-Code Customization)

- `print_settings.json` di `storage/app/public/` — company_name, logo, header_footer, paper_format
- Branch-specific settings di tabel `branches`

## 4. Skema Database Fisik (PostgreSQL DDL)

> ⚠️ **EXCLUDED tables:** cases, case_evidence, case_decisions, case_related, case_status_history, tracking_event_timings, sla_engine_jobs, decision_review, audit_login_sessions

```sql
-- users
CREATE TABLE users (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone_number VARCHAR(20),
    role VARCHAR(30) NOT NULL
        CHECK (role IN ('Admin','Head_of_Branch','Leader_Supervisor','Executive_Viewer','Staf')),
    password VARCHAR(255) NOT NULL,
    branch_id UUID REFERENCES branches(id),
    remember_token VARCHAR(100),
    created_at TIMESTAMP WITH TIME ZONE,
    updated_at TIMESTAMP WITH TIME ZONE
);

-- branches
CREATE TABLE branches (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    branch_code VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    city VARCHAR(255),
    address TEXT,
    province_code VARCHAR(10),
    regency_code VARCHAR(10),
    district_code VARCHAR(10),
    village_code VARCHAR(15),
    created_at TIMESTAMP WITH TIME ZONE,
    updated_at TIMESTAMP WITH TIME ZONE
);

-- regions (sync dari emsifa.com, daily cron 02:00 WIB)
CREATE TABLE regions (
    code VARCHAR(15) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    level VARCHAR(20) NOT NULL
        CHECK (level IN ('province','regency','district','village')),
    parent_code VARCHAR(15) REFERENCES regions(code),
    created_at TIMESTAMP WITH TIME ZONE,
    updated_at TIMESTAMP WITH TIME ZONE
);

-- shipments
CREATE TABLE shipments (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    resi_no VARCHAR(50) UNIQUE NOT NULL,
    package_id UUID REFERENCES packages(id),
    origin_branch_id UUID REFERENCES branches(id),
    destination_branch_id UUID REFERENCES branches(id),
    sender_name VARCHAR(255),
    sender_phone VARCHAR(20),
    sender_address TEXT,
    receiver_name VARCHAR(255),
    receiver_phone VARCHAR(20),
    receiver_address TEXT,
    total_koli INTEGER,
    total_weight NUMERIC(10,2),
    service_type VARCHAR(50),
    shipment_status VARCHAR(30) NOT NULL
        CHECK (status IN ('Created','In Transit','Arrived at Transit','Arrived at Destination','Out for Delivery','Delivered','On Hold','Cancelled')),
    created_at TIMESTAMP WITH TIME ZONE,
    updated_at TIMESTAMP WITH TIME ZONE
);

-- packages (koli detail)
CREATE TABLE packages (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    shipment_id UUID REFERENCES shipments(id),
    package_code VARCHAR(50),
    package_sequence INTEGER,
    koli INTEGER,
    weight NUMERIC(8,2),
    length NUMERIC(8,2),
    width NUMERIC(8,2),
    height NUMERIC(8,2),
    qr_tag VARCHAR(255),
    created_at TIMESTAMP WITH TIME ZONE
);

-- manifests
CREATE TABLE manifests (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    manifest_no VARCHAR(50) UNIQUE NOT NULL,
    origin_branch_id UUID REFERENCES branches(id),
    destination_branch_id UUID REFERENCES branches(id),
    vehicle_no VARCHAR(50),
    seal_number VARCHAR(100),
    manifest_status VARCHAR(20) NOT NULL
        CHECK (status IN ('Draft','Assigned','Dispatched','Received','Completed')),
    dispatch_time TIMESTAMP WITH TIME ZONE,
    receive_time TIMESTAMP WITH TIME ZONE,
    created_at TIMESTAMP WITH TIME ZONE,
    updated_at TIMESTAMP WITH TIME ZONE
);

-- manifest_items
CREATE TABLE manifest_items (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    manifest_id UUID REFERENCES manifests(id),
    shipment_id UUID REFERENCES shipments(id),
    created_at TIMESTAMP WITH TIME ZONE
);

-- tracking_events
CREATE TABLE tracking_events (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    shipment_id UUID REFERENCES shipments(id),
    event_type VARCHAR(50) NOT NULL,
    location VARCHAR(255),
    notes TEXT,
    event_time TIMESTAMP WITH TIME ZONE NOT NULL,
    actor_id UUID REFERENCES users(id),
    created_at TIMESTAMP WITH TIME ZONE
);

-- audit_logs
CREATE TABLE audit_logs (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    actor_id UUID REFERENCES users(id),
    actor_role VARCHAR(50),
    action VARCHAR(100) NOT NULL,
    action_type VARCHAR(50) NOT NULL
        CHECK (action_type IN ('LOGIN','LOGOUT','CREATE','UPDATE','DELETE','DISPATCH','RECEIVE','ADJUST_KOLI','UPLOAD_POD')),
    ip_address VARCHAR(45),
    user_agent TEXT,
    details JSONB,
    created_at TIMESTAMP WITH TIME ZONE
);
```

## 5. Kontrak & Spesifikasi API (RESTful Endpoints)

### 5.1 Public Tracking API (Customer-Facing & Zero PII)

```
GET  /api/tracking/{resi}       → search resi, return sanitized tracking history
POST /api/tracking/search       → search resi by partial no, Zero PII
```

> ⚠️ **EXCLUDED:** `/api/cases/*` endpoints — lihat project lama §5.3.

### 5.2 Internal Operational APIs (Staff & Manajemen)

```
POST /api/auth/login            → email+password → token
POST /api/auth/logout           → revoke token

GET  /api/shipments             → list (branch-scoped)
POST /api/shipments             → create (Staf+)
GET  /api/shipments/{id}        → view detail (cross-branch via withoutGlobalScopes)
PUT  /api/shipments/{id}/adjust-koli → adjust koli (Staf+, before departure)
POST /api/shipments/{id}/pod    → upload POD image (Staf+)

GET  /api/manifests             → list (branch-scoped)
POST /api/manifests             → create draft (Staf+)
GET  /api/manifests/{id}        → view detail
PUT  /api/manifests/{id}/dispatch   → dispatch (origin branch only)
PUT  /api/manifests/{id}/receive    → receive (destination branch only)

GET /api/regions/provinces
GET /api/regions/regencies?province={code}
GET /api/regions/districts?regency={code}
GET /api/regions/villages?district={code}

GET /print/shipments/{id}/label     → resi label PDF
GET /print/manifests/{id}/sheet     → manifest sheet PDF
GET /print/shipments/list           → shipment list PDF (branch filter)
```

## 6. Keamanan, RBAC & Isolasi Data

### 6.1 Pembatasan Sesi Bersamaan (Configurable Concurrent Session Limit)
- Default: 3 sesi aktif per user
- Configurable via env `SESSION_CONCURRENT_LIMIT`

### 6.2 Data Scoping Berbasis Cabang (Branch Scoping)
- BranchScope trait di model Shipment, Manifest, Package
- `booted()` → `static::addGlobalScope(new BranchScope)`
- Branch column: `public $branchColumn` (overridable per model)
- `withoutGlobalScopes()` untuk cross-branch view (manifest receive, shipment view)

### 6.3 RBAC
- Middleware `role` — check `auth()->user()->role`
- Direktur (Executive_Viewer) → read-only, semua write → 403
- Staf → only their branch
- Admin → full akses semua

## 7. Checkpoint dan Langkah Selanjutnya

- **Checkpoint 2:** Implementation Roadmap (T4)
- **Checkpoint 3:** CP-006 minimal project setup

## 8. Instruksi untuk Developer / Implementor

1. Gunakan UUID primary keys everywhere
2. BranchScope di `booted()` — bukan `boot()`
3. Sanctum `tokenable_id` = UUID
4. Semua FK Select field = UUID
5. Docker Compose: php-fpm + postgres + redis + minio + nginx. NO worker.
6. Jangan implement tabel atau endpoint apa pun untuk Case Management.

---

**End of document — LT-MIN-T3-001 v1.0-draft (Ready for implementation)**
