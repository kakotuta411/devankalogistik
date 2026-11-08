# RBAC — Role & Menu Breakdown

## Roles
| Role | Kode | Deskripsi |
|------|------|-----------|
| Administrator | Admin | Full access semua menu, read/write |
| Direktur/Management | Management_View | View only, semua data (no branch scope) |
| Staff Invoice | Staff_Invoice | Read/write shipment, manifest, invoice, PO, customer |
| Staff Shipment | Staff_Shipment | Read/write shipment dan manifest only |

## Menu Aktif (Navbar)
| Menu | URL | Admin | Management_View | Staff_Invoice | Staff_Shipment |
|------|-----|-------|-----------------|---------------|----------------|
| Dashboard | /dashboard | RW | R | RW* | RW* |
| Shipments | /shipments | RW | R | RW | RW |
| Manifests | /manifests | RW | R | RW | RW |
| Invoices | /invoices | RW | R | RW | R |
| Purchase Orders | /purchase-orders | RW | R | RW | — (hidden) |
| Audit Log | /audit-logs | RW | R | — (hidden) | — (hidden) |

*R = Read only, RW = Read/Write, — = tidak terlihat

## Menu Tersembunyi (Admin URL only)
| Menu | URL | Akses |
|------|-----|-------|
| Branches | /branches | Admin only |
| Users | /users | Admin only |
| Template Management | /admin/template-management | Admin only |
| Audit Log | /audit-logs | Admin + Management_View (read-only) |

## Permission Methods (User Model)
| Method | Admin | Management_View | Staff_Invoice | Staff_Shipment |
|--------|-------|-----------------|---------------|----------------|
| canWrite() | ✓ | ✗ | ✓ | ✓ |
| canManageShipments() | ✓ | ✗ | ✓ | ✓ |
| canManageManifests() | ✓ | ✗ | ✓ | ✓ |
| canManageInvoices() | ✓ | ✗ | ✓ | ✗ |
| canManageCustomers() | ✓ | ✗ | ✓ | ✗ |
| canManagePurchaseOrders() | ✓ | ✗ | ✓ | ✗ |
| canViewAuditLog() | ✓ | ✓ | ✗ | ✗ |
| isSuperAdmin() | ✓ | ✗ | ✗ | ✗ |

## Detail Akses per Role

### Administrator (Admin)
- Semua menu visible
- Read/Write semua modul
- Akses hidden menus (Branches, Users, Template Management)
- Tidak terkena BranchScope (lihat semua branch)

### Direktur/Management (Management_View)
- Menu visible: Dashboard, Shipments, Manifests, Invoices, Purchase Orders, Audit Log
- Read only (canWrite = false) — tombol create/edit/delete/submit hidden
- Tidak terkena BranchScope (lihat semua branch)
- Tidak bisa akses hidden menus (Branches, Users, Template Management)

### Staff Invoice (Staff_Invoice)
- Menu visible: Dashboard, Shipments, Manifests, Invoices, Purchase Orders
- Read/Write: Shipments, Manifests, Invoices, Purchase Orders, Customers
- Terkena BranchScope (hanya data branch sendiri)
- Tidak bisa akses hidden menus

### Staff Shipment (Staff_Shipment)
- Menu visible: Dashboard, Shipments, Manifests
- Read/Write: Shipments, Manifests
- Read only: Invoices (bisa lihat, tidak bisa create/edit)
- Purchase Orders menu hidden
- Terkena BranchScope (hanya data branch sendiri)
- Tidak bisa akses hidden menus

## API Endpoints (Permission)
| Endpoint | Admin | Management_View | Staff_Invoice | Staff_Shipment |
|----------|-------|-----------------|---------------|----------------|
| POST api/customers | ✓ | ✗ | ✓ | ✗ |
| GET api/customers/search | ✓ | ✓ | ✓ | ✓ |
| GET api/customers/{id}/shipments | ✓ | ✓ | ✓ | ✓ |
| POST api/check-po | ✓ | ✓ | ✓ | ✓ |

## Catatan
- BranchScope: non-Admin & non-Management_View hanya lihat data branch sendiri (origin OR destination untuk shipment/manifest)
- ReadOnlyForViewer middleware: blokir semua write untuk Management_View
- Hidden menus diakses via URL langsung (tidak ada di navbar)