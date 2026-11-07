# Checkpoint — Current State

## Last Updated: 2026-11-07 (session 5)

## Completed Features
1. Shipment CRUD (create, edit, show, list) with customer dropdown, PO number, payment type
2. Manifest flow: Draft → Dispatched → Received → Completed (4 steps, Out for Delivery removed)
3. Invoice management: consolidated invoicing, manual costs, partial payment, PO grouping, partial payment split
4. Purchase Order management: auto-create from shipment, linked to invoice, status tracking (draft/active/paid/partial)
5. Customer management: search, auto-create, region auto-fill
6. Public tracking: limited PII (resi, date, sender, receiver, timeline, receiver name + delivery date)
7. Print: resi detail (A4 landscape, absolute-positioned, matches original PDF), koli label (10x10cm thermal, Code 128 barcode, multi-page)
8. Export: CSV + PDF (with preview modal) for shipments, manifests, invoices
9. RBAC: Admin, Management_View, Staff_Invoice, Staff_Shipment
10. BranchScope: data isolation by branch
11. Region sync: 35 provinces, 514 regencies, 7,215 districts, 80,532 villages
12. Audit trail: all CRUD operations logged
13. Template management: per doc type settings (paper format, margins, logo)
14. Partial payment split: parent invoice locked, child invoice created with remaining balance, linked via parent_invoice_id
15. Invoice-PO auto-link: invoice auto-detects po_id from selected shipments' po_number

## Hidden Menus (admin access via URL)
- Branches (/branches)
- Users (/users)
- Template Management (/admin/template-management)
- Purchase Orders (/purchase-orders)

## RBAC Update (2026-11-07 session 4)
- Staff_Invoice: read/write shipment, manifest, invoice, PO, customer (sebelumnya hanya invoice & customer)
- Staff_Shipment: read/write shipment & manifest only (tetap)
- Management_View: view only semua menu (tetap)
- Admin: full access (tetap)
- canManageShipments/canManageManifests: ditambah Staff_Invoice
- canManagePurchaseOrders: method baru (Admin, Staff_Invoice)
- PO views: permission check ditambah
- Dokumen baru: docs/referensi/11-rbac-breakdown.md, docs/referensi/12-tracking-api.md (public tracking API + WordPress HTML/JS), docs/referensi/13-tracking-page.html (halaman tracking standalone)

## Recent Changes (2026-11-07 session 5)

### Print Settings (PrintSettingComposer.php)
- Company name default: "PT. Devanka Logistik Indonesia" (was "Logistik Tracking System")
- Header text: "Kami siap menangani setiap kebutuhan logistik Anda. Hubungi kami sekarang!"
- Footer text: "PT. Devanka Logistik Indonesia - Simpan dokumen ini sebagai bukti pengiriman"

### Print Templates (resi-detail.blade.php, resi-label.blade.php)
- resi-detail: absolute positioning dari PDF asli (9.5×5.5in), auto-resize text (max-width + word-wrap)
- resi-label: 10×10cm thermal, barcode Code 128 (GD), KOLI X/Y badge

### Tracking API (TrackingController.php)
- entry_date include jam (d/m/Y H:i)
- receiver_name = nama penerima dari shipment (bukan nomor manifest)
- tracking_events event_time format d/m/Y H:i
- CORS enabled (Access-Control-Allow-Origin: *)

### RBAC Update
- Staff_Invoice: read/write shipment, manifest, invoice, PO, customer
- Staff_Shipment: read/write shipment & manifest only
- canManagePurchaseOrders() method baru
- Dokumen: docs/referensi/11-rbac-breakdown.md

### Dokumen Baru
- docs/referensi/11-rbac-breakdown.md — RBAC breakdown
- docs/referensi/12-tracking-api.md — Public tracking API + WordPress HTML/JS
- docs/referensi/13-tracking-page.html — Halaman tracking standalone (2-col, Bootstrap 5.3.3)

## CI/CD & Deploy (2026-11-07)
- .github/workflows/ci.yml — CI: PHP 8.3, MariaDB 11 service, migrate, test, lint
- .github/workflows/deploy.yml — CD: SSH deploy to shared hosting (git pull, composer, migrate, cache, optimize)
- docs/referensi/14-deploy-github-cicd.md — setup guide (secrets, server setup)
- No git remote configured yet — user perlu create repo & push

## Recent Changes (2026-11-07 session 6)
- resi-label.blade.php: logo ditambahkan diatas koli badge
- layouts/app.blade.php: navbar fix (search + signout align kanan, overflow hidden)

## Recent Changes (2026-11-07 session 7)
- resi-detail.blade.php: logo ditambahkan di pojok kiri atas

## Recent Changes (2026-11-07 session 8)
- InvoiceController: subtotal = totalLayanan (bukan 0) di store & update
- DashboardController: pending invoice count/amount include child invoices (removed whereNull filter)
- InvoiceController::index: pending count/amount include child invoices
- PO index: card Aktif = count/sum PO yang belum lunas (status != paid)
- PO index: card "PO Partial/Pending" → "PO Total" (count + sum semua PO)

## Recent Changes (2026-11-07 session 9)
- recordPayment: parent status → Paid (bukan Partial) saat partial payment
- Display logic: invoice list tampilkan amount_paid jika punya child, total_amount jika tidak
- Eager load childInvoices di index & filteredInvoices
- Card pending invoice include child invoices (removed whereNull filter)

## Deploy Status
- VPS: Helipod (user has VPS ready)
- GitHub deploy: mulai setup
- CI/CD workflows: .github/workflows/ci.yml + deploy.yml sudah dibuat
- Deploy guide: docs/referensi/14-deploy-github-cicd.md

## Pending
- Staf UI testing (create 5 shipments, 2 invoices, 3 manifests, print all docs)
- Template: user will verify new resi layout matches original PDF
- Cleanup: existing demo data (3 POs, 12 invoices, 12 shipments)

## Recent Changes (2026-11-07 session 3)

### Template Redesign (resi detail)
- Extracted exact coordinates from original PDF (docs/referensi/resi.html)
- Rewrote resi-detail.blade.php with absolute positioning (9.5x5.5in page)
- Auto-resize: max-width + word-wrap on all text containers
- PrintController: page size changed from A4 landscape to [0,0,684,396] (9.5x5.5in)
- resi-label.blade.php: table-based layout (domPDF compatible), Code 128 barcode via GD

### Purchase Order Flow
- PO auto-created when shipment created with po_number + customer_id
- PO total_amount = 0 initially (no placeholder calc)
- PO total set from invoice total when invoice created (only if still 0)
- PO status: draft → active (invoice created) → paid/partial (payment recorded)
- Partial payment updates PO paid_amount and status

### Invoice-PO Auto-Link
- InvoiceController::store() auto-detects po_id from selected shipments' po_number
- If all selected shipments share one PO number, invoice is linked to that PO
- Existing invoice DLI-INV-202611-00009 manually linked to PO Po-demo-9272727271

### Partial Payment Split
- Migration: added parent_invoice_id (nullable UUID FK) to invoices table
- Invoice model: parentInvoice() and childInvoices() relationships
- recordPayment (partial): auto-creates new invoice for remaining balance
- New (child) invoice gets parent_invoice_id = root parent (original invoice)
- Parent invoice locked: cannot edit, cannot receive more payments
- Edit/update/payment forms hidden on parent invoice show page
- Show view: displays linked child invoices and parent invoice as clickable links
- Chain support: grandchild links to original parent (root), not immediate child

### Unused Code Cleanup
- Removed ManifestController::outForDelivery() (dead method, no route)
- Removed placeholder PO total calculation from ShipmentController

### Database Changes (2026-11-07)
- Migration: 2026_11_07_000001_recreate_purchase_orders.php (purchase_orders table + po_id on invoices)
- Migration: 2026_11_07_000002_add_parent_invoice_to_invoices.php (parent_invoice_id on invoices)
- Existing PO totals updated to match linked invoice totals (or 0 if no invoice)

## Recent Changes (2026-11-07 session 4)

### PO & Invoice Sync (Backend)
- PO paid_amount dihitung dari sum amount_paid semua invoice (bukan hanya Paid invoices)
- PO status auto-sync saat view index/show: paid (jika semua lunas), partial (jika ada pembayaran), active (jika belum ada pembayaran)
- Pending invoice count/amount exclude child invoices (whereNull parent_invoice_id) untuk hindari double-counting
- markPaid juga sync PO status

### PO Index View
- Tombol "Buat PO" dihapus
- Card warna: Aktif=biru, Draft=abu, Partial/Pending=kuning, Lunas=hijau, Tertunggak=merah
- Card "PO Tertunggak" baru: kuantiti PO dengan sisa > 0 + total pending payment
- Tombol "Edit" dihapus dari list PO

### PO Detail View
- Tombol "Edit" dihapus
- Tombol "Buat Invoice" auto-fill customer & checklist shipment via po_id param

### Invoice Create (dari PO)
- Auto-select customer dari PO
- Auto-check semua shipment dari PO
- po_id hidden input terkirim
- Customer data auto-fill (nama, telepon, alamat, email, region)

### Invoice Show View
- Badge warna: Draft=biru, Partial=kuning, Paid=hijau, Cancelled=merah
- Tombol "Tandai Lunas" di-hide jika ada child invoices
- Catatan "Sisa dari invoice XXX" jadi clickable link ke parent
- Tunggakan = 0 jika ada child invoices

### Invoice List View
- Total pakai total_amount (bukan grandTotal) untuk hindari parent/child sama
- Badge warna: Draft=biru, Partial=kuning, Paid=hijau
- Pending count/amount exclude child invoices

### Data Cleanup
- Semua data transaksional dihapus untuk testing dari awal (session 4)

## RBAC Restructure (2026-11-06)
Changed from 5 roles to 4 roles:
- Admin (full access)
- Management_View (read-only, all data, no branch scope)
- Staff_Invoice (CRUD invoices + customers, view shipments/manifests, branch scoped)
- Staff_Shipment (CRUD shipments + manifests, view invoices, branch scoped)

Files updated:
- User model: canManageShipments(), canManageManifests(), canManageInvoices(), canManageCustomers()
- BranchScope: bypass for Admin + Management_View
- ReadOnlyForViewer middleware: blocks writes for Management_View
- StoreUserRequest/UpdateUserRequest: role validation updated
- users/create, edit, index views: role dropdown updated
- All module views: button visibility per role
- Migration: default role changed to Staff_Shipment
- DB: existing Executive_Viewer → Management_View

## Git Deploy
- Git repo initialized
- deploy.sh created (pull → composer → migrate → cache → permissions)
- .gitignore updated (mysql_data/ excluded)