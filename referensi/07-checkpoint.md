# Checkpoint — Current State

## Last Updated: 2026-11-13

## Project Info
- **Name**: devanka-logistik
- **Repo**: github.com/kakotuta411/devankalogistik (private)
- **VPS**: 43.173.50.217 / devankalogistik.web.id
- **Framework**: Laravel 13 (PHP 8.3), MariaDB 11, Bootstrap 5.3.3
- **Local Dev**: Docker (port 8080)
- **Production**: Native PHP-FPM + Nginx + MariaDB (no Docker)

## Completed Features

### Core
1. Shipment CRUD (create, edit, show, list) with customer dropdown, PO number, payment type
2. Manifest flow: Draft → Dispatched → Received → Completed (4 steps, Out for Delivery removed)
3. Invoice management: consolidated invoicing, manual costs, partial payment, PO grouping, partial payment split
4. Purchase Order management: auto-create from shipment, linked to invoice, status tracking
5. Customer management: search, auto-create, region auto-fill
6. Public tracking API: limited PII (resi, date, sender, receiver, timeline, receiver name + delivery date), CORS enabled
7. Print: resi detail (A4 landscape, absolute-positioned), koli label (10×10cm thermal, Code 128 barcode, multi-page)
8. Export: CSV + PDF (with preview modal) for shipments, manifests, invoices
9. RBAC: Admin, Management_View, Staff_Invoice, Staff_Shipment
10. BranchScope: data isolation by branch
11. Region sync: 35 provinces, 514 regencies, 7,215 districts, 80,532 villages
12. Audit trail: all CRUD operations logged
13. Template management: per doc type settings (paper format, margins, logo)
14. Partial payment split: parent invoice locked, child invoice created with remaining balance
15. Invoice-PO auto-link: auto-detect po_id from selected shipments' po_number
16. PO auto-create on shipment store (total=0 until invoice created)
17. WordPress tracking page: standalone HTML (2-col Bootstrap 5.3.3, timeline, status badge)

### RBAC Breakdown
| Role | Shipments | Manifests | Invoices | PO | Customers | Branches | Users |
|------|-----------|-----------|----------|-----|-----------|----------|-------|
| Admin | RW | RW | RW | RW | RW | RW | RW |
| Management_View | R | R | R | R | R | R | R |
| Staff_Invoice | RW | RW | RW | RW | RW | - | - |
| Staff_Shipment | RW | RW | R | - | - | - | - |

### Invoice Calculation Formula
- total_layanan = (shipping_cost × weight) + packaging_cost + equipment_charge + other_costs
- total_amount = total_layanan - discount + ppftz03 + ppn + pph
- discount = percentage of total_layanan

### PO Flow
1. Shipment created with po_number + customer_id → auto-create PO (total=0, status=draft)
2. Invoice created from PO → auto-detect po_id → PO total set from invoice total (if still 0), status=active
3. Payment recorded:
   - Full payment → invoice status=Paid, PO paid_amount += amount, status=active/paid
   - Partial payment → parent status=Paid (locked), child invoice created for remainder, PO paid_amount += amount
4. PO status sync: paid (all invoices paid), partial (has payment), active (no payment)
5. If PO has shipment in active (non-cancelled) invoice → exclude entire PO from selection

### Partial Payment Split
- Migration: parent_invoice_id (nullable UUID FK) on invoices
- recordPayment (partial): auto-creates child invoice for remaining balance
- Child invoice: parent_invoice_id = root parent (original invoice)
- Parent locked: cannot edit, cannot receive more payments
- Chain support: grandchild links to original parent (root)
- Display: invoice list shows amount_paid if has child, total_amount if not

### Print Types
1. Koli label: 10×10cm thermal, Code 128 barcode (GD), KOLI X/Y badge, multi-page
2. Resi detail: half-A4 (9.5×5.5in landscape), absolute positioning from original PDF, logo top-left

### Public Tracking API
- GET /api/tracking/search?resi= (min 3 chars)
- GET /api/tracking/{resi} (limited PII, CORS enabled)
- entry_date includes time (d/m/Y H:i)
- receiver_name from shipment data
- tracking_events event_time format d/m/Y H:i

## Hidden Menus (admin access via URL)
- Branches (/branches)
- Users (/users)
- Template Management (/admin/template-management)
- Purchase Orders (/purchase-orders)

## Key Technical Decisions
- PO auto-create on shipment store (total=0 until invoice created)
- Partial payment: parent status = Paid (not Partial), child for remainder, parent locked
- Invoice list total: amount_paid if has child, else total_amount
- PO status sync: paid (all invoices paid), partial (has payment), active (no payment)
- PO paid_amount = sum amount_paid all invoices
- Pending invoice = all invoices including child (removed whereNull filter)
- PO index: Aktif card = status != paid; Total card = all POs
- Code 128 barcode (GD) replaces QR in label template
- Resi detail uses absolute positioning from original PDF (9.5×5.5in)
- Public tracking API: CORS enabled, limited PII, entry_date includes time
- RBAC: Staff_Invoice gets shipment+manifest+invoice+PO+customer; Staff_Shipment gets shipment+manifest only
- CI/CD: CI runs tests on push/PR; CD deploys via SSH on push to master
- DB_TIMEZONE=+07:00 (avoid MySQL timezone table issues)
- storage/framework/{sessions,views,cache/data} must exist (gitignored, create manually on fresh deploy)

## Deploy History (VPS)

### Setup
- VPS: Ubuntu 24.04, PHP 8.3, MariaDB 11, Nginx
- IP: 43.173.50.217, Domain: devankalogistik.web.id
- GitHub: kakotuta411/devankalogistik (private)
- CI/CD: GitHub Actions (ci.yml + deploy.yml)

### Issues Encountered & Fixes
1. **Migration cleanup_unused FK drop error** → `Can't DROP FOREIGN KEY invoices_po_id_foreign` → Remove migration file entirely
2. **RegionSeeder duplicate entry** → `Duplicate entry '11' for key PRIMARY` → Drop & recreate DB
3. **DummyDataSeeder JKT/BTM not found** → "Run base seeder first" → Create branches/users via tinker first
4. **MySQL timezone unknown** → `Unknown or incorrect time zone` → Use DB_TIMEZONE=+07:00
5. **Permission denied on storage** → `Failed to clear cache` → chown www-data:www-data
6. **Missing storage/framework directories** → `No such file or directory` for sessions/views/cache → mkdir manually
7. **CI test 302 vs 200** → ExampleTest expects 200 but GET / returns 302 (auth redirect) → Fix test to GET /login
8. **Login failed** → User not found or wrong password → Reset password via tinker
9. **Nginx not configured** → Default page showing → Create site config, enable, reload
10. **Connection timed out** → Firewall/Cloudflare → Ensure Nginx listening on port 80

### Final Status
- Site accessible via IP (43.173.50.217) and hostname (devankalogistik.web.id)
- Login working: admin@logistik.test / password
- All migrations applied, regions seeded
- Auto-deploy via GitHub Actions active

## Recent Changes (2026-11-08)
- CI fix: example test GET /login (not /) due to auth redirect
- VPS deploy: devankalogistik.web.id, IP 43.173.50.217
- DB_TIMEZONE=+07:00 (avoid timezone table issue)
- Docs renamed from docs/ to referensi/
- All reference docs updated with comprehensive info

## Recent Changes (2026-11-08 RBAC Update)
- RBAC updated: Audit Log viewable by Admin + Management_View (read-only)
- User model: canViewAuditLog() method added
- Route: /audit-logs moved to role:Admin,Management_View group
- Navbar: Audit Log menu visible for Admin + Management_View
- Updated 11-rbac-breakdown.md with new permissions

## Recent Changes (2026-11-09 Phase 1 COMPLETE)
- Phase 1 VPS Security: ALL 15 items ✅ (UFW, Fail2ban, Nginx headers, tuning, backup, monitoring)
- Region sync: 75,412 villages (data source variance from 80,532 target)
- DB user least privilege: devanka has SELECT/INSERT/UPDATE/DELETE only
- CORS restricted: devankalogistik.com + www
- deploy.yml: sudo -u www-data for artisan commands
- check-phase1.sh: automated verification script (15 checks, all ✅)
- setup-phase1-remaining.sh: automated setup script
- fix-phase1.sh: fix script for services/MariaDB/region sync
- GitHub Actions: check-phase1.yml, setup-phase1-remaining.yml, fix-phase1.yml
- check-phase1.sh: all 15 items ✅ (0 failed, 0 warnings)
## Recent Changes (2026-11-08 Phase 2)
- Audit Log view (admin-only): controller, route, view, navbar menu — commit 7202411
- Rate limiting: login 5/min, tracking 30/min, api 60/min — commit 502866c
- CORS restricted: devankalogistik.com + www.devankalogistik.com (was *)
- Storage permissions fix (permission denied on deploy): added to Phase 1 checklist
- Session security: secure=true default, http_only, same_site — commit 502866c
- Custom error pages: 404, 500, 403 (Bootstrap 5) — commit 502866c
- .env.example: SESSION_SECURE_COOKIE, SESSION_SAME_SITE, SESSION_HTTP_ONLY — commit 502866c
- AppServiceProvider: RateLimiter definitions
- routes/api.php: throttle middleware on health/login/tracking
- config/session.php: secure default true
- Tests: 2/2 pass

## Recent Changes (2026-11-09 Invoice/PO UI)
- Card "Invoice Lunas": total dihitung dari `sum(amount_paid)` (bukan `sum(total_amount)`), jadi parent + child invoice yang terbayar menjumlah = total PO
- Card invoice (Lunas/Pending) & PO (Aktif/Draft/Total/Lunas/Tertunggak) sekarang bisa diklik → modal berisi list data
- Tabel invoice: urutan kolom Customer, PO, No Invoice, Status, Total, Terbayar, Tanggal Pembayaran, Tanggal Dibuat, Aksi
- PO controller: koleksi kartu/modal diambil dari semua PO (bukan hanya halaman aktif)
- Invoice controller: eager load `payments` + `po` untuk kolom tanggal pembayaran & PO

## Recent Changes (2026-11-09 Workflow Cleanup)
- Workflow one-off dihapus: diagnose-500, fix-redis-ext, diagnostic-app, fix-phase1, setup-phase1-remaining
- Script turunan dihapus: diagnostic-app.sh, fix-phase1.sh, setup-phase1-remaining.sh
- Workflow disimpan: ci.yml, deploy.yml, check-phase1.yml, region-sync-force.yml, restart-fpm.yml, setup-redis-opcache.yml
- referensi/18 diperbarui: status COMPLETE, panduan Redis + OPcache, daftar workflow

## Recent Changes (2026-11-09 PO Export)
- Menu PO: export PDF + Excel (CSV) — endpoint `/purchase-orders/export/pdf` & `/purchase-orders/export/csv`
- PDF preview modal (sama seperti menu Invoice), kolom: No PO, Pelanggan, Total, Dibayar, Sisa, Status, Dibuat
- View print: `print/purchase-order-list.blade.php`
- Controller: `PurchaseOrderController::exportCsv/exportPdf/filteredPos`

## Recent Changes (2026-11-09 Template Breakdown)
- Dokumen baru `referensi/19-template-breakdown.md`: daftar file template per dokumen + data yang bisa ditarik
- Menemukan route mati: `/admin/template-management/upload` (method `uploadTemplate` tidak ada)

## Recent Changes (2026-11-09 Child Invoice + Dashboard Fix)
- Child invoice (pecahan pembayaran): tampilan TOTAL pakai `displayTotal()` (= sisa), bukan `grandTotal()` (yang salah hitung ulang dari kolom biaya copakan parent)
- `Invoice::displayTotal()` & `Invoice::isChild()` baru di model
- Ringkasan Finansial child invoice: tampil "Sisa dari invoice induk" (breakdown biaya tidak ditampilkan)
- PDF invoice parent: breakdown LENGKAP (Biaya Pengiriman rate×kg, Packaging, Charge Alat, Biaya Lain-lain, Diskon, PPFTZ 03, PPN, PPH)
- Dashboard card "Invoice Lunas": total = `sum(amount_paid)` (konsisten dgn menu Invoice)

## Recent Changes (2026-11-09 Dashboard Revamp)
- Tabel "Recent Shipments" dihapus, diganti 2 tabel: **Invoice Pending** & **PO Pending**
- Kolom tabel: Customer | Progress Pembayaran (bar + %) | Sisa Tunggakan
- Top 10 sisa tunggakan terbesar; baris bisa diklik ke detail invoice/PO
- Progress: invoice = amount_paid/displayTotal; PO = paid_amount/total_amount
- Warna bar: <30% merah, 30-69% kuning, ≥70% hijau
- 5 kartu & modal Shipments/Manifests/Invoices tetap ada

## Recent Changes (2026-11-09 Region v2 + Navbar)
- Region sync diupgrade ke emsifa v2: 38 provinsi, 514 kabupaten, 7.285 kecamatan, 83.762 desa (dari 33/479/6.604/75.412)
- Format kode berubah: v1 flat (`1301011001`) → v2 titik (`13.09.10.2002`)
- Data existing (1 customer, 7 invoice, 3 shipment) dimigrasi ke kode v2 via `regions:migrate-v2`
- Sync dioptimasi: concurrency 30 request/batch + bulk upsert (dari ~85 menit jadi ~8 menit)
- `RegionSyncService`: parse `data` wrapper, try/catch resilient, skip district yang sudah synced
- Menu Dashboard disembunyikan untuk role Staff_Invoice & Staff_Shipment
- Workflow `region-migrate-v2.yml` untuk migrasi production

## Recent Changes (2026-11-13 Customer Create from Sender Data)

- **Create Customer from Sender Data**: New button "Buat Customer Baru (dari data pengirim)" on shipment create page
- **Flow**: Admin fills sender fields (name, phone, address, region) → clicks button → POST to `/api/customers` → page reloads → new customer appears in dropdown
- **Data mapping**: `sender_name` → `name` + `company_name` (both), `sender_phone`, `sender_address`, `sender_*_code` → customer region fields
- **Removed**: Modal-based customer creation (was buggy with region cascading)
- **CSRF fix**: Added `<meta name="csrf-token">` to layout + `credentials: 'same-origin'` on all fetch calls
- **API auth**: Fixed fetch calls to send session cookies (API endpoints under `auth` middleware)

## Pending (per 2026-11-13)

### Manual (perlu user/VPS console)
- [ ] **Template resi**: user verifikasi layout resi baru cocok dengan PDF asli
- [ ] **Deploy changes to VPS**: Push to GitHub → GitHub Actions deploy.yml → verify on devankalogistik.web.id

### Data
- [ ] **Cleanup demo data**: data dummy (Anang, DLI-00001..00006, invoice dummy) mau dihapus?

### Ops (opsional, ditunda user)
- [ ] **Cloudflare DNS**: skip dulu — nanti kalau banyak domain/app
- [ ] **SSH hardening**: skip (user request)
- [ ] **IdP/SSO login**: skeleton login pakai IdP kalau sudah ada (sekarang X-LDAP-Operator-DN header)

### Selesai sesi ini
- [x] RBAC (Audit Log utk Admin + Management_View)
- [x] CORS restrict ke devankalogistik.com
- [x] Phase 1 VPS (17/17 ✅) + Phase 2 Laravel security
- [x] Redis + OPcache production mode
- [x] Invoice/PO: card lunas fix, card clickable + modal, export PDF/Excel PO
- [x] Child invoice display fix (displayTotal)
- [x] Dashboard: tabel Invoice Pending + PO Pending (progress bar)
- [x] Region v2 migration (38/514/7.285/83.762)
- [x] Menu Dashboard hidden utk Staff_Invoice & Staff_Shipment
- [x] Workflow one-off dibersihkan (6 workflow inti tersisa)
- [x] UptimeRobot (site health 5 min)
- [x] Region sync berkala (workflow `region-sync-scheduled.yml`, tanggal 1 03:00 UTC)
- [x] rclone Backblaze B2 (config + cron offsite backup 05:00, bucket `devanka-backup`)
- [x] monitor.sh Opsi B (8 cek, cron 5 min, alert Slack webhook)
- [x] Setup workflows: `setup-monitor.yml`, `final-ops.yml`
- [x] Resi detail: layout fix (logo, kotak NO RESI/PO, divider, spacing)
- [x] Resi label koli: dompdf-safe layout (wrap 412pt, logo 30pt, footer/tagline hidden)
- [x] Fix login 419 (SESSION_SECURE_COOKIE=false)
- [x] Docs: template-breakdown + dompdf quirks section

## Documentation Index
- 01-overview.md — Project info, roles, prefixes, formulas
- 02-architecture.md — Docker, production, middleware, region sync, print
- 03-database.md — 23 tables, columns, key fields
- 04-api-endpoints.md — All routes (web + API + print + public tracking)
- 05-models-relations.md — 14 models, relations, RBAC methods, patterns
- 06-environment.md — .env config (dev + production), storage paths
- 07-checkpoint.md — This file (state, changelog, deploy history)
- 08-production-deploy.md — VPS deploy guide
- 09-agent-manual.md — Agent quick start, commands, patterns, deploy history
- 10-database-schema.md — ERD text diagram, table summary
- 11-rbac-breakdown.md — RBAC breakdown
- 12-tracking-api.md — Public tracking API + WordPress HTML/JS
- 13-tracking-page.html — Standalone tracking page
- 14-deploy-github-cicd.md — CI/CD setup guide
- 15-vps-security.md — Phase 1: VPS security hardening (firewall, SSH, fail2ban, SSL, tuning)
- 16-vps-monitoring-backup.md — Phase 1: Monitoring, backup, resource analysis
- 17-laravel-security.md — Phase 2: Laravel security (health check, rate limit, CORS, session, audit)
- 18-phase1-implementation.md — Phase 1 VPS implementation tracker (SSH skipped)
- 19-template-breakdown.md — Template dokumen: file & data per jenis dokumen