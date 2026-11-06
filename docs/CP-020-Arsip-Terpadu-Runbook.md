# CP-020 — Arsip Checkpoint Terpadu + Runbook

**Project:** akllogistik-minimal  
**Stack:** Laravel 13.39 / PHP 8.3 / PostgreSQL 15 / Redis 7 / MinIO / Bootstrap 5.3.3 (local, no CDN)  
**Scope:** Core Logistics Tracking — Shipment, Manifest, Branch, Region sync, Tracking Events, Audit Trail, Invoices, Print templates  
**Excluded:** Filament, Case Management, Redis queue worker  

---

## 🔄 1. Status Flow

### 1.1 Shipment Status
```
Created → In Transit → Arrived at Destination → Out for Delivery → Delivered
         (dispatch)     (manifest receive)       (manifest Ofd)    (manifest complete)
```
- **Trigger sync**: setiap manifest workflow action otomatis update shipment status + create TrackingEvent
- **Adjust koli**: hanya sebelum dispatch (manifest masih Draft)
- **POD upload**: setelah Delivered

### 1.2 Manifest Status
```
Draft → Dispatched → Received → Out for Delivery → Completed
```
- **Dispatch**: hanya origin branch atau Admin
- **Receive**: hanya destination branch atau Admin
- **Out for Delivery**: hanya setelah Received
- **Complete**: hanya setelah Out for Delivery
- **Seal number**: otomatis `SEAL-{manifest_no}` jika kosong

### 1.3 Invoice Status
```
Draft → Paid / Cancelled
```
- **Mark Paid**: Staf+ · **Mark Cancelled**: Staf+ (hanya saat Draft)
- **Branch auto-fill**: invoice_create branch_id di-take dari user login (hidden input + readonly display)

### 1.4 Resi Format
- Shipment: `TRK-{YYYYMMDD}-{SEQ:5}`
- Manifest: `MNF-{YYYYMMDD}-{SEQ:5}`
- Invoice: `INV-{YYYYMM}-{SEQ:5}`
- Seal: `SEAL-{manifest_no}`

---

## 🗄️ 2. Database Schema (12 tabel)

| Tabel | PK | Branch Column | Scope |
|-------|----|---------------|-------|
| users | UUID | branch_id (nullable FK) | — |
| branches | UUID | — | — |
| regions | code (string) | — | — |
| shipments | UUID | origin_branch_id | BranchScope |
| packages | UUID | — | — |
| manifests | UUID | origin_branch_id | BranchScope |
| manifest_items | UUID | — | pivot |
| tracking_events | UUID | shipment_id FK | — |
| invoices | UUID | branch_id | BranchScope |
| invoice_items | UUID | — | pivot |
| audit_logs | UUID | — | — |
| personal_access_tokens | bigint | — | — |

**Region code format (bukan UUID):**
- province: `32`, regency: `3201`, district: `3201010`, village: `3201010001`

---

## 🔌 3. API Endpoints

### 3.1 Web Routes (Blade, session auth)

| Method | Path | Controller | Auth | Role |
|--------|------|------------|------|------|
| GET | `/login` | AuthController@showLogin | ❌ | — |
| POST | `/login` | AuthController@login | ❌ | — |
| POST | `/logout` | AuthController@logout | ✅ | — |
| GET | `/dashboard` | DashboardController@index | ✅ | — |
| GET | `/shipments` | ShipmentController@index | ✅ | — |
| GET | `/shipments/create` | ShipmentController@create | ✅ | canWrite |
| POST | `/shipments` | ShipmentController@store | ✅ | canWrite |
| GET | `/shipments/{id}` | ShipmentController@show | ✅ | — |
| GET | `/shipments/{id}/edit` | ShipmentController@edit | ✅ | — |
| PUT | `/shipments/{id}` | ShipmentController@update | ✅ | — |
| POST | `/shipments/{id}/adjust-koli` | ShipmentController@adjustKoli | ✅ | — |
| GET | `/manifests` | ManifestController@index | ✅ | — |
| GET | `/manifests/create` | ManifestController@create | ✅ | canWrite |
| POST | `/manifests` | ManifestController@store | ✅ | canWrite |
| GET | `/manifests/{id}` | ManifestController@show | ✅ | — |
| GET | `/manifests/{id}/edit` | ManifestController@edit | ✅ | — |
| POST | `/manifests/{id}/dispatch` | ManifestController@dispatch | ✅ | — |
| POST | `/manifests/{id}/receive` | ManifestController@receive | ✅ | — |
| POST | `/manifests/{id}/out-for-delivery` | ManifestController@outForDelivery | ✅ | — |
| POST | `/manifests/{id}/complete` | ManifestController@complete | ✅ | — |
| GET | `/invoices` | InvoiceController@index | ✅ | — |
| GET | `/invoices/create` | InvoiceController@create | ✅ | canWrite |
| POST | `/invoices` | InvoiceController@store | ✅ | canWrite |
| GET | `/invoices/{id}` | InvoiceController@show | ✅ | — |
| POST | `/invoices/{id}/mark-paid` | InvoiceController@markPaid | ✅ | — |
| POST | `/invoices/{id}/mark-cancelled` | InvoiceController@markCancelled | ✅ | — |
| GET | `/branches` | BranchController@index | ✅ | Admin, Head_of_Branch |
| GET | `/branches/create` | BranchController@create | ✅ | Admin, Head_of_Branch |
| POST | `/branches` | BranchController@store | ✅ | Admin, Head_of_Branch |
| GET | `/branches/{id}/edit` | BranchController@edit | ✅ | Admin, Head_of_Branch |
| GET | `/users` | UserController@index | ✅ | Admin |
| GET | `/users/create` | UserController@create | ✅ | Admin |
| POST | `/users` | UserController@store | ✅ | Admin |
| GET | `/admin/template-management` | PrintSettingController@index | ✅ | Admin |
| POST | `/admin/template-management` | PrintSettingController@store | ✅ | Admin |
| POST | `/admin/template-management/upload` | PrintSettingController@uploadTemplate | ✅ | Admin |
| GET | `/print/shipments/{id}/label` | PrintController@shipmentLabel | ✅ | — |
| GET | `/print/manifests/{id}/sheet` | PrintController@manifestSheet | ✅ | — |
| GET | `/print/invoices/{id}/pdf` | PrintController@invoice | ✅ | — |
| GET | `/print/shipments/list` | PrintController@shipmentList | ✅ | — |

### 3.2 Region API (web auth, untuk cascading selects)

```
GET /api/regions/provinces
GET /api/regions/regencies?province={code}
GET /api/regions/districts?regency={code}
GET /api/regions/villages?district={code}
```

### 3.3 Config Items yang INERT (jangan gunakan)

- `agents:` + `routing_keywords` di config.yaml → **tidak dipakai** di project ini (project Laravel, bukan multi-role Slack gateway)
- `model.fallback_models` → **tidak ada** di project ini
- `hermes config set` list values → pakai JSON array syntax

---

## 🔒 4. RBAC & Data Scoping

| Role | Shipment | Manifest | Invoice | Branch | User | Template Mgmt |
|------|----------|----------|---------|--------|------|---------------|
| Admin | Full | Full | Full | Full | Full | Full |
| Head_of_Branch | Read/Write cabangnya | Dispatch/Receive cabangnya | Read/Write cabangnya | Full (buat/manage) | Read | Read |
| Leader_Supervisor | Read cabangnya | View | Read cabangnya | Read | Read | Read |
| Executive_Viewer | 🔍 Read-only | 🔍 Read-only | 🔍 Read-only | 🔍 | 🔍 | 🔍 |
| Staf | Read/Write cabangnya | Dispatch/Receive | Read/Write cabangnya | ❌ | ❌ | ❌ |

- **BranchScope**: global scope di Shipment (origin_branch_id), Manifest (origin_branch_id), Invoice (branch_id)
- `withoutGlobalScopes()` dipakai untuk cross-branch view (manifest show, shipment detail, invoice detail)
- Non-Admin hanya lihat data cabangnya

---

## 🌍 5. Region Sync

- **Source:** `https://www.emsifa.com/api-wilayah-indonesia/api`
- **Command:** `php artisan regions:sync`
- **Cron:** daily `02:00 WIB`
- **Data volume:** 35 provinsi, 514 kab/kota, 7,215 kecamatan, 80,532 kelurahan
- **Region model:** `scopeProvinces()`, `scopeRegencies($code)`, `scopeDistricts($code)`, `scopeVillages($code)`

---

## 📄 6. Print Templates

| Template | Paper | Content |
|----------|-------|---------|
| `resi-label.blade.php` | Thermal 10x10cm | QR code (chillerlan/php-qrcode), resi, koli, weight, region chain |
| `manifest-sheet.blade.php` | A4 | Daftar muatan, driver, seal number, 3 signature columns |
| `invoice.blade.php` | A4 | Customer info, shipment table (Layanan + Harga columns), region chain |
| `shipment-list.blade.php` | A4 | Daftar kiriman per cabang |

- **Settings:** `storage/app/public/print_settings.json` (per doc type: invoice, manifest, shipment)
- **Logo:** `storage/app/public/logos/` (converted to base64 in PrintSettingComposer untuk domPDF offline)
- **domPDF config:** `enable_remote=false` → semua image harus base64, arrow `&rarr;` → ASCII `->`

---

## ⚙️ 7. Environment Config

### Docker ports
| Service | Port |
|---------|------|
| postgres | 5433 |
| redis | 6380 |
| minio | 9010/9011 |
| app (php) | 9002 |
| nginx | 8001 |

### .env penting
```env
APP_NAME=Logistik Tracking Minimal
APP_URL=http://localhost:8001
DB_TIMEZONE=Asia/Jakarta
SHIPPING_RATE_REGULAR=10000
SHIPPING_RATE_EXPRESS=15000
SESSION_LIFETIME=120
```

### Startup order
1. postgres + redis → tunggu pg_isready → migrate
2. Baru start app + nginx + minio

---

## 🏃 8. Runbook: Daily Operations

### 8.1 Start System
```bash
cd /home/ambon/akllogistik-minimal
docker compose up -d
docker exec lt-min-app php artisan migrate --force
```

### 8.2 Login
- URL: `http://localhost:8001/login`
- Admin: `admin@logistik.test` / `password`

### 8.3 Region Sync Manual
```bash
docker exec lt-min-app php artisan regions:sync
```

### 8.4 Print Dokumen
- Resi label: `http://localhost:8001/print/shipments/{id}/label`
- Manifest sheet: `http://localhost:8001/print/manifests/{id}/sheet`
- Invoice: `http://localhost:8001/print/invoices/{id}/pdf`

### 8.5 Template Management
- URL: `http://localhost:8001/admin/template-management`
- Upload logo: `public/images/logo.png`
- Settings: company_name, paper_format, header/footer text — disimpan per doc type di `storage/app/public/print_settings.json`

### 8.6 Navbar
- Nav pills (Dashboard, Shipments, Manifests, Invoices, Branches, Users, Template Mgmt)
- Logo klik → `/dashboard`
- Sign Out → tombol `btn-warning` di kanan navbar

---

## 🔧 9. Known Config Gotchas

1. **`agents:` + `routing_keywords`** di config.yaml — inert untuk Laravel project ini
2. **`model.fallback_models`** — config ini tidak ada di project ini (khusus Hermes gateway)
3. **Session domain** = `null` (auto-detect) — bisa cause cross-domain session loss
4. **Cookie-based auth** — semua web route pakai session cookie, bukan API token
5. **domPDF `enable_remote=false`** — logo harus base64, semua asset lokal

---

## ✅ 10. Verifikasi Terakhir (2026-10-29)

- Semua route web return 200
- Navbar: nav-pills, light theme (#E3F2FD), logo + Sign Out button
- Shipment create/edit: "Pengiriman" section (Cabang Tujuan + Jenis Layanan) di atas "Detail Paket"
- Invoice create: branch auto-fill dari user login, tidak ada "Konsolidasi" list group
- Branch edit: tidak ada city text area, city otomatis dari regency region
- Manifest: edit via modal di show page (status Draft only)
- PHP lint clean pada semua file

---

**End of CP-020 — Arsip Checkpoint Terpadu + Runbook**
