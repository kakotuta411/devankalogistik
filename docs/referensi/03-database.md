# Database Schema

## Tables (22)

### Core
| Table | PK | Description |
|-------|-----|-------------|
| `users` | uuid | User accounts (role, branch_id) |
| `branches` | uuid | Branch locations (code, name, city, region codes) |
| `shipments` | uuid | Shipment records (resi_no, sender/receiver, customer_id, po_number, payment_type, region codes) |
| `manifests` | uuid | Manifest records (manifest_no, origin/dest branch, status, driver_name) |
| `manifest_items` | uuid | Pivot: manifest ↔ shipment (belongsToMany) |
| `invoices` | uuid | Invoice records (invoice_no, customer, costs, payment tracking) |
| `invoice_items` | uuid | Pivot: invoice ↔ shipment |
| `invoice_payments` | uuid | Partial payment records |
| `tracking_events` | uuid | Shipment tracking timeline |
| `customers` | uuid | Customer data (name, company, phone, email, address, region codes) |
| `regions` | string (code) | Indonesian regions (province/regency/district/village) |
| `packages` | uuid | Package types |
| `audit_logs` | uuid | Audit trail |

### Laravel System
| Table | Description |
|-------|-------------|
| `migrations` | Migration history |
| `cache`, `cache_locks` | File cache (DB table fallback) |
| `sessions` | Session storage |
| `jobs`, `job_batches`, `failed_jobs` | Queue (unused, sync mode) |
| `password_reset_tokens` | Password reset |
| `personal_access_tokens` | Sanctum API tokens |

### Removed/Unused
| Table | Status |
|-------|--------|
| `purchase_orders` | ❌ Not used (PO grouped from shipments) |

## Key Columns

### shipments
- resi_no, customer_id (FK customers), po_number, payment_type (kredit/lunas/cod)
- origin_branch_id, destination_branch_id (FK branches)
- sender_name, sender_phone, sender_address, sender_province/regency/district/village_code
- receiver_name, receiver_phone, receiver_address, receiver_province/regency/district/village_code
- total_koli, total_weight, service_type (REGULER/EXPRESS), package_description
- shipment_status (Created/In Transit/Arrived at Destination/Delivered/On Hold/Cancelled)

### invoices
- invoice_no, branch_id, customer_id, po_id (unused)
- customer_name, customer_phone, customer_address, customer_email, customer_province/regency/district/village_code
- subtotal, total_koli, total_weight
- shipping_cost, packaging_cost, equipment_charge, ppftz03, ppn, pph, discount_type, discount_value, other_costs, discount, amount_paid, total_amount
- invoice_status (Draft/Paid/Cancelled), invoice_date, due_date, notes

### customers
- name, company_name, phone, email, address
- province_code, regency_code, district_code, village_code

### manifests
- manifest_no, origin_branch_id, destination_branch_id
- manifest_status (Draft/Dispatched/Received/Completed)
- vehicle_no, driver_name, dispatch_time, receive_time
