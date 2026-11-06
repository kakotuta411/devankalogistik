# Logistik Tracking — PRD v1.0-draft (Minimal Laravel)

**Document ID:** LT-MIN-PRD-001  
**Project:** akllogistik-minimal  
**Framework:** Laravel 11, PHP 8.3  
**Database:** PostgreSQL 15  
**UI:** Laravel Blade (NO Filament)  
**Scope:** Core Logistics Tracking — NO Case Management  
**PIC:** @ambon  
**Status:** Draft — Ready for execution

---

## 1. Tujuan

Sistem pencatatan tracking logistik minimal tanpa Case Management dan tanpa Filament.  
Fokus pusat: **shipment tracking, manifest antar cabang, regional data, print template.**

---

## 2. Cakupan (IN SCOPE)

- 📦 **Shipment** — resi, koli, status tracking, sender/receiver info
- 🚛 **Manifest** — daftar muatan antar cabang, dispatch/receive, seal number auto
- 🏢 **Branch** — cabang asal/tujuan, kode cabang, wilayah
- 🌏 **Region** — provinsi/kota/kecamatan/kelurahan (sync via API emsifa.com, cron 02:00 WIB)
- 📄 **Print Template** — resi label, manifest sheet, shipping list (Blade + DomPDF)
- 🔧 **Audit Trail** — login + CRUD aktivitas user
- 👤 **User Management** — role-based: Admin, Head_of_Branch, Staf, Executive_Viewer
- 🎨 **Logo/Print Settings** — upload logo, header/footer text, paper format

## 3. Di Luar Lingkup (OUT OF SCOPE)

- ❌ Case Management (koli kurang, hilang, rusak, dsb.)
- ❌ Filament admin panel — pakai Laravel Blade penuh
- ❌ SLA case, eskalasi, keputusan head
- ❌ Loss decree (SK Kehilangan) — bagian dari Case Management
- ❌ Report weekly/monthly otomatis

---

## 4. Format Identitas

- **Resi:** `TRK-{YYYYMMDD}-{SEQ:5}` → `TRK-20261027-00001`
- **Manifest:** `MNF-{YYYYMMDD}-{SEQ:5}` → `MNF-20261027-00001`
- **Seal Number:** `SEAL-{manifest_no}` auto-generate jika kosong
- **User UUID:** primary key `uuid`

---

## 5. Role & Kewenangan

| Role | Shipment | Manifest | Branch | Region Sync | Audit Trail |
|---|---|---|---|---|---|
| **Admin** | ✅ Full | ✅ Full | ✅ Full | ✅ Full | ✅ Full |
| **Head_of_Branch** | ✅ Read/Write cabangnya | ✅ Dispatch/Receive cabangnya | ✅ Manage cabangnya | ❌ | ✅ Read |
| **Leader_Supervisor** | ✅ Read cabangnya | ✅ View | ✅ Read | ❌ | ✅ Read |
| **Executive_Viewer** | 🔍 Read-only | 🔍 Read-only | 🔍 Read-only | ❌ | 🔍 Read |
| **Staf** | ✅ Read/Write cabangnya | ✅ Dispatch/Receive | ❌ | ❌ | ✅ Read |

**BranchScope:** model shipment/manifest/branch otomatis filter berdasarkan `branch_id` user yang login. Kecuali Admin (akses semua).

**Direktur (Executive_Viewer):** tidak bisa create/edit/delete — hanya lihat.

---

## 6. Database Schema (Essential Tables)

```
users
  - id (uuid, PK)
  - name, email, phone_number, role (enum), branch_id (FK)
  - remember_token, timestamps

branches
  - id (uuid, PK)
  - branch_code (unique), name, city, address
  - province_code, regency_code, district_code, village_code
  - timestamps

regions
  - code (PK), name, level (enum:province/regency/district/village), parent_code
  - sync via emsifa.com API, daily cron 02:00 WIB

shipments
  - id (uuid, PK)
  - resi_no (unique), package_id (FK→packages)
  - origin_branch_id (FK→branches), destination_branch_id (FK→branches)
  - sender_name, sender_phone, sender_address
  - receiver_name, receiver_phone, receiver_address
  - total_koli, total_weight, service_type
  - shipment_status (enum: Created, In Transit, Arrived at Transit,
    Arrived at Destination, Out for Delivery, Delivered, On Hold, Cancelled)
  - timestamps

packages
  - id (uuid, PK), shipment_id (FK→shipments),
  - package_code, package_sequence, koli, weight
  - dimensions, qr_tag

manifests
  - id (uuid, PK), manifest_no (unique)
  - origin_branch_id (FK), destination_branch_id (FK)
  - vehicle_no, seal_number (auto: SEAL-{manifest_no})
  - manifest_status (enum: Draft, Assigned, Dispatched, Received, Completed)
  - dispatch_time, receive_time, timestamps

manifest_items
  - id (uuid, PK), manifest_id (FK), shipment_id (FK)
  - timestamps

tracking_events
  - id (uuid, PK), shipment_id (FK)
  - event_type (enum), location, notes, event_time, actor_id (FK→users)
  - timestamps

audit_logs
  - id (uuid, PK), actor_id (FK→users), action, action_type,
  - ip_address, user_agent, details (jsonb), timestamps
  - action CHECK constraint: LOGIN, LOGOUT, CRUD_SHIPMENT, CRUD_MANIFEST, etc.

print_settings (stored as JSON in storage/app/print_settings.json)
  - company_name, company_logo (path), header_text, footer_text, paper_format
```

---

## 7. API Endpoints

### Public (tanpa auth)
```
GET  /api/tracking/{resi}       → cari resi, return tracking history
POST /api/tracking/search       → search resi by partial no
```

### Auth (Sanctum token)
```
POST /api/auth/login            → email+password → token
POST /api/auth/logout           → revoke token
```

### Shipment
```
GET    /api/shipments            → list (branch-scoped)
POST   /api/shipments            → create (Staf+)
GET    /api/shipments/{id}       → view detail (cross-branch via withoutGlobalScopes)
PUT    /api/shipments/{id}/adjust-koli → adjust koli (Staf+, before departure)
POST   /api/shipments/{id}/pod   → upload POD image (Staf+)
```

### Manifest
```
GET  /api/manifests             → list (branch-scoped)
POST /api/manifests             → create draft (Staf+)
GET  /api/manifests/{id}        → view detail
PUT  /api/manifests/{id}/dispatch → dispatch (origin branch only)
PUT  /api/manifests/{id}/receive  → receive (destination branch only)
```

### Region
```
GET /api/regions/provinces
GET /api/regions/regencies?province={code}
GET /api/regions/districts?regency={code}
GET /api/regions/villages?district={code}
```

### Print
```
GET /print/shipments/{id}/label     → resi label PDF (10x10cm)
GET /print/manifests/{id}/sheet     → manifest sheet PDF (A4)
GET /print/shipments/list           → shipment list PDF (A4, branch filter)
```

---

## 8. Filament Resource Mapping → Blade Controller

| Filament Resource | Laravel Blade Equivalent |
|---|---|
| ShipmentResource | ShipmentController (index, create, show, edit) |
| ManifestResource | ManifestController (index, create, dispatch, receive) |
| BranchResource | BranchController (index, create, show) |
| UserResource | UserController (index, create, show, edit) |
| RegionResource → | RegionController (read-only, no CRUD form) |
| AuditLogResource → | AuditController (read-only) |
| TemplateManagement page → | PrintSettingController (upload logo, save settings) |

---

## 9. Print Templates

1. **resi-label.blade.php** — 10x10cm thermal, QR Code + resi + koli info
2. **manifest-sheet.blade.php** — A4 truk manifest, daftar muatan + seal number
3. **shipment-list.blade.php** — A4 daftar kiriman per cabang

Templates pake `$logoUrl`, `$printSettings['company_name']`, `header_text`, `footer_text` via ViewComposer `PrintSettingComposer`.

---

## 10. Audit Trail

- **Login event** → AuditLoginListener ( Illuminate\Auth\Events\Login )
- **CRUD log** → AppServiceProvider boot(), panggil `AuditLog::create()` di setiap controller method create/update/delete
- Fields: actor_id, actor_role, action, ip, user_agent, details (JSON), timestamps

---

## 11. Regional Data Sync

- **Source API:** `https://www.emsifa.com/api-wilayah-indonesia/api`
- **Command:** `php artisan regions:sync`
- **Cron:** daily `02:00 WIB` via `routes/console.php`
- **Target:** 34 provinsi, 514 kabupaten/kota, 7215 kecamatan, ~83k desa
- **Model:** Region (code, name, level, parent_code) — self-referencing parent

---

## 12. Deployment Stack

```
docker-compose.yml
  services:
    - postgres:15 (port 5432, db: logistik_db)
    - redis:7     (port 6379, no password)
    - minio:latest (S3 gateway, bucket: logistik-tracking)
    - app: php:8.3-fpm-alpine (phpredis, composer, dompdf, intervention)
    - nginx (port 8000)
    - worker:queue (php artisan queue:work)
```

---

## 13. Next Steps

1. `composer create-project laravel/laravel` di `/home/ambon/akllogistik-minimal`
2. Buat migration semua tabel (users, branches, regions, shipments, packages, manifests, tracking_events, audit_logs)
3. Generate UUID PK di semua model, enable HasFactory
4. Install dompdf, intervention/image, league/flysystem-s3-v3
5. Buat controller: AuthController, ShipmentController, ManifestController, BranchController, UserController, RegionController, PrintController
6. Buat Blade views: auth (login), layouts (app), pages (dashboard, shipments, manifests, branches, users, regions, audit)
7. Middleware: auth, role, branchScope
8. Buat API routes (auth + CRUD + print)
9. ViewComposer untuk print settings
10. Region sync command + cron

---

## 14. Differences dari Project Asli (Full Filament)

| Aspek | Full Project | Minimal Project |
|---|---|---|
| UI Framework | Filament v3 (admin panel) | Laravel Blade manual |
| Case Management | ✅ Full | ❌ Excluded |
| Admin Panel | Filament Resources/Pages | Manual Blade Controllers |
| Dashboard | Filament Widgets | Custom Blade dashboard |
| Auth | Filament Panel Login | Laravel built-in Auth |
| Print Settings | Filament Page | Controller + Blade view |
| API | Filament API + custom | Laravel API Resources |

---

**End of document — LT-MIN-PRD-001 v1.0-draft (Draft — ready for execution)**
