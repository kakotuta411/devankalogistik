# CP-HOSTING-001 — Migrasi Shared Hosting (MariaDB + File Cache + Local Disk)

**Project:** /home/ambon/akl-logistik-hosting
**Source:** /home/ambon/akllogistik-minimal
**Date:** 2026-10-31
**Status:** ✅ Docker dev environment running (MariaDB + PHP-FPM + Nginx)

---

## Yang Sudah Dikerjakan

### Database Migration
- PostgreSQL → MariaDB 11
- Migration files fixed: jsonb→json, removed CHECK constraints, removed duplicate seal_number
- All 15 migrations ran successfully
- Database seeded: 10 branches, 10 users, 10 shipments, 10 manifests, 35 provinces + region data

### Cache & Storage
- Redis → File cache (`storage/framework/cache/data`)
- MinIO/S3 → Local disk (`storage/app/public/`)
- Filesystem disk changed from `local` to `public`

### Docker Setup
- `docker-compose.yml`: MariaDB 11 + PHP-FPM + Nginx (port 8080)
- `Dockerfile`: pdo_mysql, no redis, storage dirs created
- `docker/php-fpm.conf`: listen 9000 for cross-container access
- `docker/nginx/default.conf`: port 80, fastcgi_pass app:9000

### Environment
- `.env`: MySQL config, file cache, public disk, no Redis/AWS
- `.env.example`: Production template for shared hosting

### Verification
- HTTP 200 OK on http://localhost:8080
- Laravel 13.39.0, PHP 8.3.36
- Database seeded successfully
- Login page renders correctly

## Yang Belum / Pending

- [ ] Test full CRUD flow (shipment, manifest, invoice) di Docker
- [ ] Test print templates (resi, invoice, manifest)
- [ ] Test region cascading selects
- [ ] Test POD upload (local disk)
- [ ] Test logo upload (local disk)
- [ ] Deploy ke shared hosting (Hostinger/Niagahoster/DomaiNesia)
- [ ] Setup cron job untuk region sync di hosting
- [ ] Optimize untuk production (config:cache, route:cache, view:cache)

## Credentials (Docker Dev)

| Item | Value |
|------|-------|
| DB Host | mysql (Docker) / 127.0.0.1 (hosting) |
| DB Port | 3306 |
| DB Name | logistik_min |
| DB User | logistik |
| DB Password | logistikpass123 |
| DB Root Password | rootpassword |
| App URL | http://localhost:8080 |
| Nginx Port | 8080 |

## Test Accounts

| Email | Password | Role | Branch |
|-------|----------|------|--------|
| admin@logistik.test | *** | Admin | - |
| user1@logistik.test | *** | Admin | Jakarta Hub |
| user5@logistik.test | *** | Staf | Makassar |
| user10@logistik.test | *** | Staf | Balikpapan |

## Docker Commands

```bash
# Start
cd /home/ambon/akl-logistik-hosting
docker compose up -d

# Stop
docker compose down

# Rebuild
docker compose build --no-cache

# Migrate
docker exec lt-hosting-app php artisan migrate --force

# Seed
docker exec lt-hosting-app php artisan db:seed --force

# Clear cache
docker exec lt-hosting-app php artisan config:clear
docker exec lt-hosting-app php artisan cache:clear
docker exec lt-hosting-app php artisan view:clear

# Logs
docker logs lt-hosting-app
docker logs lt-hosting-nginx
docker logs lt-hosting-mariadb
```

## Verification Results (2026-10-31)

- ✅ Docker containers: MariaDB + PHP-FPM + Nginx running on port 8080
- ✅ Laravel 13.39.0, PHP 8.3.36
- ✅ Database seeded: 10 branches, 10 users, 10 shipments, 10 manifests
- ✅ Login page: HTTP 200
- ✅ Dashboard: HTTP 200 (after login with admin@logistik.test / password)
- ✅ Storage directories: cache/data, cache/locks, views, sessions, logs, public
- ✅ File cache: working (storage/framework/cache/data)
- ✅ Session: working (storage/framework/sessions)
- ✅ View compilation: working (storage/framework/views)

## Docker Status

```
lt-hosting-mariadb    Up (healthy)
lt-hosting-app        Up
lt-hosting-nginx      Up (port 8080→80)
```

---

## Update: Bug Fixes & Flow Simplification (2026-10-31)

### 1. Region Indonesia Sync
- Region data: 33 provinces (seeder). Full sync (regencies/districts/villages) butuh internet ke emsifa.com.
- Command: `php artisan regions:sync` (jalankan di hosting jika outbound HTTP diizinkan).
- Alternatif: import SQL dump dari project asli.

### 2. Template Management Menu — Hidden
- Menu "Template Mgmt" dihapus dari navbar (semua level user).
- Route masih accessible via URL langsung.

### 3. Dashboard Table Size — Diseragamkan
- Dashboard table: `table table-hover align-middle` (sama seperti shipments).
- Hapus `table-sm` dan `table-responsive small`.

### 4. Navbar Covering Title on Resize — Fixed
- Tambah media query `@media (max-width: 991.98px)` → padding-top 5.5rem.

### 5. Seal Number — Dihapus
- Removed from: Model, Controller, API, Request, Views, Print template, Seeder.
- DB column `seal_number` tetap ada (tidak dipakai).

### 6. Flow Pengiriman — Disimplifikasi
- **Sebelum:** Created → In Transit → Arrived → Out for Delivery → Delivered (5 step)
- **Sesudah:** Created → In Transit → Arrived at Destination → Delivered (4 step)
- Removed "Out for Delivery" step. Setelah Received, staff bisa langsung "Selesaikan".
- Route `out-for-delivery` dihapus dari web.php.
- Views: tombol "Selesaikan" muncul setelah status "Received".

### Verification
- ✅ Docker rebuild + migrate + seed berhasil
- ✅ Home: 302, Login: 200, Dashboard: 200
- ✅ Template Mgmt menu tidak tampil di navbar
- ✅ Seal number tidak tampil di manifest views
- ✅ Flow: Dispatch → Receive → Complete (tanpa Out for Delivery)

---

## Update: Bug Fixes & Export Feature (2026-10-31)

### Bug Fix 1: Edit User Password Kosong
- **Root cause:** Laravel `nullable` validation converts empty string → null, lalu `$user->update($data)` set password = null (MySQL NOT NULL).
- **Fix:** `unset($data['password'])` jika kosong.

### Bug Fix 2: Create Invoice - Field 'amount' doesn't have default value
- **Root cause:** `invoice_items.amount` NOT NULL tanpa default, controller pakai `attach()` yang tidak set amount.
- **Fix:** Ganti `attach()` dengan manual `InvoiceItem::create()` yang menghitung amount = total_weight × rate.

### Feature: Export PDF & Export Excel (CSV)
- **Routes:** `/shipments/export/pdf`, `/shipments/export/csv`
- **Controller:** `ShipmentController::exportPdf()`, `exportCsv()`, `filteredShipments()`
- **View:** `print/shipment-list.blade.php` (domPDF)
- **Buttons:** "Export PDF" (merah) + "Export Excel" (hijau) di halaman shipments index
- **Filter:** Export mengikuti filter yang aktif (status, branch, date range, search)
- **CSV:** BOM UTF-8 untuk Excel compatibility

### Verification
- ✅ CSV export: 200, 11 lines (header + 10 data)
- ✅ PDF export: 200, 3524 bytes
- ✅ Filter works: status=Created → 2 rows
- ✅ User edit (empty password): 302 (success)
- ✅ Invoice create: 302 (success), amount = 125000.00

---

## Update: Export Filter + Pagination Fix (2026-11-01)

### Issue 1: Export tidak mengikuti filter
- **Root cause:** JS FormData approach tidak reliable; href awal "#" tidak ter-update.
- **Fix:** Blade `request()->getQueryString()` untuk set initial href dengan filter params. JS tetap update on change.

### Issue 2: Pagination tidak muncul
- **Root cause:** `paginate(15)` dengan 10 seeded items = 1 halaman, links() kosong.
- **Fix:** Ubah `paginate(15)` → `paginate(5)` di ShipmentController, ManifestController, InvoiceController. Sekarang 10 items = 2 halaman, pagination muncul.

### Verification
- ✅ Export PDF/CSV href includes query params (e.g., `?status=Created`)
- ✅ Export filter works: status=Created → 2 rows
- ✅ Pagination: shipments (page 1/2), manifests (page 1/2)
- ✅ Invoices: no data seeded, but code ready

---

## Update: Pagination 10 + Data Tambahan (2026-11-01)

### Perubahan
1. **Pagination:** 5 → 10 per halaman (Shipments, Manifests, Invoices, Branches, Users)
2. **Data:** DummyDataSeeder diperluas dari 10 → 15 items per menu (branches, users, shipments, manifests)
3. **Status arrays:** diperluas dari 10 → 15 items
4. **Cities/codes/provinces:** diperluas dari 10 → 15

### Verifikasi
- ✅ Shipments: 15 data, pagination 10/halaman (page 1/2)
- ✅ Manifests: 15 data, pagination (page 1/2)
- ✅ Branches: 15 data, pagination (page 1/2)
- ✅ Users: 16 data (1 admin + 15 users), pagination (page 1/2)
- ✅ Invoices: 0 data (no seeded invoices, code ready)

---

## Update: BranchScope + PDF Preview Modal (2026-11-01)

### 1. BranchScope Update
- **Before:** User hanya melihat data berdasarkan origin_branch_id
- **After:** User melihat data berdasarkan origin_branch_id ATAU destination_branch_id
- **Files:** BranchScope.php, Shipment.php, Manifest.php (added destinationBranchColumn property)

### 2. PDF Preview Modal
- **Before:** Export PDF langsung download
- **After:** Klik "Export PDF" → modal preview muncul → iframe menampilkan PDF → tombol "Download PDF" untuk download
- **Controller:** exportPdf() support `?preview=1` param (stream instead of download)
- **Views:** shipments, manifests, invoices index (modal + JS)

### Verification
- ✅ BranchScope: property_exists check for destinationBranchColumn
- ✅ Preview endpoint: 200 application/pdf (stream)
- ✅ Download endpoint: 200 application/pdf (download)
- ✅ Modal present in all 3 views
- ✅ Iframe + Download button in modal

---

## CHECKPOINT TERAKHIR (2026-11-01) — PAUSE

### Status Project
- **Folder:** /home/ambon/akl-logistik-hosting
- **Docker:** containers running (lt-hosting-mariadb, lt-hosting-app, lt-hosting-nginx)
- **Port:** 8080
- **DB:** MySQL/MariaDB 11, bind mount ./mysql_data:/var/lib/mysql
- **Cache:** file cache (storage/framework/cache/data)
- **Data:** 16 branches, 16 users, 16 shipments, 16 manifests, 0 invoices
- **Login:** admin@logistik.test / password (Admin, Jakarta Hub)

### Fitur Selesai
1. ✅ Migration PostgreSQL → MariaDB 11
2. ✅ Docker dev setup (MariaDB + PHP-FPM + Nginx)
3. ✅ Bind mount untuk data persist (./mysql_data)
4. ✅ Manifest flow 4-step (Draft → Dispatched → Received → Completed)
5. ✅ Seal number removed
6. ✅ Template management hidden from navbar
7. ✅ Dashboard table unified with shipments
8. ✅ Navbar padding fix (bouncing)
9. ✅ Edit forms: modal-xl AJAX (no iframe)
10. ✅ Cascading region selects (provinsi→kab→kec→kel)
11. ✅ Region codes stored per entity (shipment: 8 cols, invoice: 4 cols)
12. ✅ Print templates with region chain display
13. ✅ Export PDF (red) + Export Excel (green) di Shipments, Manifests, Invoices
14. ✅ Export dengan filter (status, branch, date range, search)
15. ✅ PDF Preview Modal (iframe + download button)
16. ✅ Pagination 10 per halaman (Shipments, Manifests, Invoices, Branches, Users)
17. ✅ BranchScope: user lihat data origin ATAU destination branch
18. ✅ Bug fix: User edit empty password
19. ✅ Bug fix: Invoice create amount calculation
20. ✅ Navbar theme #E3F2FD light

### Pending / Belum Selesai
- Region full sync (butuh internet ke emsifa.com API)
- User test as Staf (create 5 shipments, 2 invoices, 3 manifests, print all docs)
- Invoice seed data (0 invoices seeded)

### Files Penting
- docker-compose.yml (bind mount mysql_data)
- app/Scopes/BranchScope.php (origin OR destination)
- app/Http/Controllers/{Shipment,Manifest,Invoice}Controller.php (export methods)
- resources/views/{shipments,manifests,invoices}/index.blade.php (export buttons + modal + pagination)
- resources/views/print/{shipment-list,manifest-list,invoice-list}.blade.php
- database/seeders/DummyDataSeeder.php (16 items per menu)
- docs/MIGRATION-GUIDE.md

### Catatan untuk Next Session
- JANGAN DROP DATABASE atau re-seed (data user persist di ./mysql_data)
- Untuk apply code changes: docker compose build app && docker compose up -d
- Untuk restart: docker compose restart app
- Bind mount aktif, data aman di folder host

## Update 2026-11-03: Region Sync + Export Filter Fix

### Region Full Sync
- Modified `RegionSyncService::syncAll()` to skip already-complete levels (provinces 35, regencies 514, districts 7215)
- Modified `syncVillages()` to skip districts with existing villages + try-catch per district
- Full sync completed: 35 provinces, 514 regencies, 7,215 districts, 80,532 villages
- Total: 88,296 region records

### Export Filter Fix
- Changed `branch_id` filter in ShipmentController + ManifestController from `origin_branch_id` only → `origin_branch_id OR destination_branch_id`
- Applied in both `index()` and `filteredXxx()` methods
- Export CSV/PDF now follows all active filters (status, search, branch, date range)
- Verified: export with branch filter returns filtered subset, export without filter returns all data

## Update 2026-11-04: Menu Hide + Tunnel

### Menu & User Changes
- Hidden Branches menu from navbar (all users)
- Hidden Users menu from navbar (all users)
- Deleted all users except admin@logistik.test (Admin) and user4@logistik.test (Executive_Viewer/direktur)
- Deleted all branches except JKT (Jakarta) and BTM (Batam)

### Cloudflare Tunnel
- Tunnel URL: https://reaches-burton-choices-quantities.trycloudflare.com
- Points to: http://localhost:8080 (akl-logistik-hosting)
- Running in background (PID 2554543)
- NOTE: Quick tunnel (trycloudflare) has no uptime guarantee. For production, use named tunnel.

## Update 2026-11-04: UI Cleanup + Package Description + Dashboard Cards + Login Logo

### Package Description Feature
- Migration: 2026_11_04_000001_add_package_description_to_shipments.php (package_description text nullable)
- Shipment model $fillable updated
- StoreShipmentRequest + UpdateShipmentRequest validation added
- Create form: input package_description (already existed)
- Edit form: added input package_description
- Show page: display "Detail Paket"
- Resi label: {{ $shipment->package_description ?? '-' }} in "Isi Barang"
- Manifest sheet: added "Detail Paket" column (7 columns total)
- Seeder: package_description = 'Paket {service} - {n} item'

### Dashboard Cards Redesign
- 3 cards now clickable → open modal with list (shipments/manifests/invoices)
- Card style: text-bg-success (green) with card-header + card-body
- Body centered (text-center)
- Auto-width: col (equal distribution)
- Modal: modal-xl, modal-dialog-scrollable, with "Lihat Semua" link
- recent_invoices added to DashboardController stats

### Login Page
- Replaced bi bi-box-seam icon with logo.png (max-height: 120px)
- Same logo as navbar (public/images/logo.png)

### Shipment Hold/Cancel
- POST /shipments/{id}/hold → status On Hold + remove from manifest_items + tracking event
- POST /shipments/{id}/cancel → status Cancelled + remove from manifest_items + tracking event
- Buttons on shipment show page (per-resi, with confirm dialog)
- Hidden for Executive_Viewer (read-only)

### Resi Label Spacing
- @page margin: 0.4cm → 0.2cm
- .wrap padding: 6px → 2px
- All internal margins reduced to 2px/0
- size: 10cm auto (auto height)

### Invoice Prefix
- InvoiceGenerator: prefix 'AKL-INV' (was 'AKL-INV' confirmed by user)
- max() query filtered by 'AKL-INV-%' prefix
- Old 'INV-' invoices cleaned up

### Seeder Update
- Prefix: TRK → AKL, MNF → AKL-MNF
- Only valid statuses (no legacy Arrived at Transit, Out for Delivery, Assigned)
- 12 shipments, 4 manifests with proper relationships
- package_description included

### Filter Dropdowns
- Shipment: removed Arrived at Transit, Out for Delivery
- Manifest: removed Assigned

### Cloudflare Tunnels (active)
- akl-logistik-hosting (port 8080): https://atlanta-graduate-brussels-binding.trycloudflare.com
- Matupa Coffee (port 80): https://essentially-strip-crowd-advertiser.trycloudflare.com
- PromotorOps (port 8081): https://closes-ended-ton-humor.trycloudflare.com

### DB State
- Branches: JKT, BTM only
- Users: admin@logistik.test (Admin), user4@logistik.test (Executive_Viewer)
- Shipments: 12 (AKL-20261104-XXXXX)
- Manifests: 4 (AKL-MNF-20261104-XXXXX)
- Invoices: 0 (cleaned)
- Regions: 88,296 (full sync)

## Client Revisions — Progress Update (2026-11-06)

### Completed
1. **Database foundation**: 5 new migrations (customers, purchase_orders, invoice_payments, shipment columns, invoice columns) — all applied
2. **Models**: Customer, PurchaseOrder, InvoicePayment created with HasUuid + keyType string. Shipment & Invoice updated with new fillable + relations
3. **Customer search API**: `/api/customers/search` + `/api/customers` store
4. **PO search API**: `/api/purchase-orders?customer_id=xxx`
5. **Shipment form**: customer search dropdown, PO number input, payment type radio (kredit/lunas/cod) — create + edit
6. **Invoice form**: customer search dropdown, PO dropdown, manual cost inputs (shipping, packaging, equipment, PPFTZ03, PPN, PPH, discount type/value, other costs), amount_paid
7. **Invoice controller**: removed auto-rate calculation, manual cost total_amount calculation, discount %/nominal support
8. **Partial payment**: recordPayment method + route + progress bar + payment history on show page
9. **PO module**: PurchaseOrderController (CRUD) + index/create/show views + navbar menu
10. **Public tracking**: limited to resi_no, entry_date, sender, receiver, status, tracking_events, receiver_name, delivered_at
11. **ShipmentController store**: passes customer_id, po_number, payment_type

### Verified
- Shipment creation with customer_id + po_number + payment_type: ✅
- Invoice creation with manual costs + total_amount calculation: ✅ (73000 = 50000+10000+5000+11000+2000-5000)
- Partial payment recording: ✅ (30000+20000=50000)
- Customer search API returns UUID: ✅
- PO search API: ✅
- PO page loads: ✅

### Remaining
- Resi print template: 2 types (colly label "koli ke X/5" + half-A4 detail with payment type)
- Invoice show page: display customer info + cost breakdown
- Invoice edit form: customer search + PO dropdown + manual costs
- Shipment show page: display customer, PO, payment type
- Edit modal JS: customer search in AJAX-loaded forms
- Template invoice redesign
- Template manifest review
