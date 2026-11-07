# Models & Relations

## Models (14 active)

| Model | Table | PK | Traits |
|-------|-------|-----|--------|
| User | users | uuid | HasUuid |
| Branch | branches | uuid | HasUuid, BranchScope |
| Shipment | shipments | uuid | HasUuid, BranchScope |
| Manifest | manifests | uuid | HasUuid, BranchScope |
| ManifestItem | manifest_items | uuid | Pivot |
| Invoice | invoices | uuid | HasUuid, BranchScope |
| InvoiceItem | invoice_items | uuid | (none) |
| InvoicePayment | invoice_payments | uuid | HasUuid |
| TrackingEvent | tracking_events | uuid | HasUuid |
| Customer | customers | uuid | HasUuid |
| Region | regions | string (code) | (none) |
| Package | packages | uuid | HasUuid |
| AuditLog | audit_logs | uuid | HasUuid |
| PurchaseOrder | purchase_orders | uuid | HasUuid |

## Relations

### Shipment
- belongsTo: Branch (origin), Branch (destination), Customer, Package
- hasMany: TrackingEvent
- belongsToMany: Manifest (via manifest_items)

### Manifest
- belongsTo: Branch (origin), Branch (destination)
- belongsToMany: Shipment (via manifest_items)

### Invoice
- belongsTo: Branch, Customer, PurchaseOrder, parentInvoice (self)
- hasMany: InvoiceItem, InvoicePayment, childInvoices (self)

### InvoiceItem
- belongsTo: Invoice, Shipment

### PurchaseOrder
- belongsTo: Customer
- hasMany: Invoice (via po_id)
- Fields: po_number (unique), total_amount, paid_amount, status (draft/active/paid/partial/cancelled), notes

### Customer
- hasMany: Shipment, Invoice, PurchaseOrder

### TrackingEvent
- belongsTo: Shipment, User (actor)

## User RBAC Methods
- canManageShipments() → Admin, Staff_Invoice
- canManageManifests() → Admin, Staff_Invoice
- canManageInvoices() → Admin, Staff_Invoice
- canManageCustomers() → Admin, Staff_Invoice
- canManagePurchaseOrders() → Admin, Staff_Invoice
- isManagementView() → Management_View
- isStaffShipment() → Staff_Shipment

## BranchScope
Applied to: Shipment, Manifest, Invoice, Branch
- Non-Admin users: filter by origin_branch_id (or destination for Shipment/Manifest)
- Executive_Viewer/Management_View: bypass (sees all)
- Admin: bypass (sees all)

## Key Patterns
- All FK fields use Select not TextInput UUID
- BranchScope in booted() of each scoped model
- Sanctum: tokenable_id must be uuid if User PK is UUID
- User PK is UUID (HasUuid trait)
- Pagination default: Bootstrap 5 (AppServiceProvider)
- API routes prefixed with api. namespace
- Modal edit: AJAX form extraction
- Region cascading: provinsi → kab/kota → kecamatan → kelurahan
- Customer search: dropdown select, auto-fill sender fields + region
- Invoice costs: all manual (no auto-rate)
- Partial payment: recordPayment + progress bar + history
- PO auto-create on shipment store (total=0)