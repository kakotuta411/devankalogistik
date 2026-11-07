# Models & Relations

## Models (13 active)

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

## Relations

### Shipment
- belongsTo: Branch (origin), Branch (destination), Customer, Package
- hasMany: TrackingEvent
- belongsToMany: Manifest (via manifest_items)

### Manifest
- belongsTo: Branch (origin), Branch (destination)
- belongsToMany: Shipment (via manifest_items)

### Invoice
- belongsTo: Branch, Customer
- hasMany: InvoiceItem, InvoicePayment, childInvoices (self)
- belongsTo: PurchaseOrder, parentInvoice (self)

### InvoiceItem
- belongsTo: Invoice, Shipment

### PurchaseOrder
- belongsTo: Customer
- hasMany: Invoice (via po_id)
- Fields: po_number (unique), total_amount, paid_amount, status (draft/active/paid/partial/cancelled)

### Customer
- hasMany: Shipment, Invoice, PurchaseOrder

### TrackingEvent
- belongsTo: Shipment, User (actor)

## BranchScope
Applied to: Shipment, Manifest, Invoice, Branch
- Non-Admin users: filter by origin_branch_id (or destination for Shipment/Manifest)
- Executive_Viewer: bypass (sees all)
- Admin: bypass (sees all)
