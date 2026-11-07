# Database Schema — AKL Logistik

## Entity Relationship Diagram (Text)

```
┌─────────────┐     ┌──────────────┐     ┌─────────────┐
│  branches   │     │    users     │     │  customers  │
│─────────────│     │──────────────│     │─────────────│
│ id (PK)     │◄────│ branch_id(FK)│     │ id (PK)     │
│ branch_code │     │ id (PK)      │     │ name        │
│ name        │     │ name         │     │ company_name│
│ city        │     │ email        │     │ phone       │
│ address     │     │ password     │     │ email       │
│ province_code│    │ phone_number │     │ address     │
│ regency_code│     │ role         │     │ province_code│
│ district_code│    │ created_at   │     │ regency_code│
│ village_code│     │ updated_at   │     │ district_code│
│ created_at  │     └──────┬───────┘     │ village_code│
│ updated_at  │            │             │ created_at  │
└──────┬──────┘            │             │ updated_at  │
       │                   │             └──────┬──────┘
       │                   │                    │
       │    ┌──────────────┴──────────────┐     │
       │    │         shipments           │     │
       │    │─────────────────────────────│     │
       │    │ id (PK)                     │◄────┘ (customer_id FK)
       │    │ resi_no (UNI)               │
       │    │ customer_id (FK)            │
       │    │ po_number                   │
       │    │ package_id (FK→packages)    │
       ├────│ origin_branch_id (FK)       │
       ├────│ destination_branch_id (FK)   │
       │    │ sender_name/phone/address   │
       │    │ sender_province/regency/    │
       │    │   district/village_code     │
       │    │ receiver_name/phone/address │
       │    │ receiver_province/regency/  │
       │    │   district/village_code     │
       │    │ total_koli                  │
       │    │ total_weight                │
       │    │ service_type                │
       │    │ payment_type (kredit/      │
       │    │   lunas/cod)                │
       │    │ package_description         │
       │    │ shipment_status             │
       │    │ created_at/updated_at       │
       │    └──────┬──────────────────────┘
       │           │
       │    ┌──────┴──────────────┐
       │    │  tracking_events    │
       │    │─────────────────────│
       │    │ id (PK)             │
       │    │ shipment_id (FK)    │
       │    │ event_type          │
       │    │ location            │
       │    │ notes               │
       │    │ event_time          │
       │    │ actor_id (FK→users) │
       │    │ created_at/updated_at│
       │    └─────────────────────┘
       │
       │    ┌──────────────┐     ┌──────────────┐
       │    │  manifests   │     │ manifest_items│ (Pivot)
       │    │──────────────│     │──────────────│
       │    │ id (PK)      │◄────│ manifest_id  │
       │    │ manifest_no  │     │ shipment_id  │
       ├────│ origin_branch_id│    │ id (PK)     │
       ├────│ destination_  │     │ created_at   │
       │    │   branch_id   │     │ updated_at   │
       │    │ vehicle_no   │     └──────────────┘
       │    │ manifest_status│
       │    │ dispatch_time │
       │    │ receive_time  │
       │    │ driver_name   │
       │    │ created_at/  │
       │    │ updated_at   │
       │    └──────────────┘
       │
       │    ┌──────────────┐     ┌──────────────┐
       │    │   invoices   │     │ invoice_items │
       │    │──────────────│     │──────────────│
       │    │ id (PK)      │◄────│ invoice_id   │
       │    │ invoice_no   │     │ shipment_id  │
       ├────│ branch_id    │     │ amount       │
       │    │ customer_id  │     │ created_at/  │
       │    │ customer_name│     │ updated_at   │
       │    │ customer_phone│    └──────────────┘
       │    │ customer_address│
       │    │ customer_province│
       │    │   /regency/    │
       │    │   district/    │
       │    │   village_code │
       │    │ subtotal       │
       │    │ total_koli     │
       │    │ total_weight   │
       │    │ shipping_cost  │
       │    │ packaging_cost │
       │    │ equipment_charge│
       │    │ ppftz03        │
       │    │ ppn            │
       │    │ pph            │
       │    │ discount_type  │
       │    │ discount_value │
       │    │ other_costs    │
       │    │ amount_paid    │
       │    │ total_amount   │
       │    │ discount       │
       │    │ invoice_status │
       │    │ invoice_date   │
       │    │ due_date       │
       │    │ notes          │
       │    │ created_at/   │
       │    │ updated_at    │
       │    └──────┬────────┘
       │           │
       │    ┌──────┴──────────────┐
       │    │  invoice_payments   │
       │    │─────────────────────│
       │    │ id (PK)             │
       │    │ invoice_id (FK)     │
       │    │ amount              │
       │    │ payment_date        │
       │    │ payment_method      │
       │    │ notes               │
       │    │ created_at/updated_at│
       │    └─────────────────────┘
       │
       │    ┌──────────────┐
       │    │   packages   │
       │    │──────────────│
       │    │ id (PK)      │
       │    │ shipment_id  │ (FK→shipments, nullable)
       │    │ package_code │
       │    │ package_sequence│
       │    │ koli         │
       │    │ weight       │
       │    │ length/width/│
       │    │   height     │
       │    │ qr_tag       │
       │    │ created_at/  │
       │    │ updated_at   │
       │    └──────────────┘
       │
       │    ┌──────────────┐
       │    │   regions    │
       │    │──────────────│
       │    │ code (PK)    │
       │    │ name         │
       │    │ level (enum) │
       │    │ parent_code  │ (self-ref FK)
       │    │ created_at/  │
       │    │ updated_at   │
       │    └──────────────┘
       │
       │    ┌──────────────┐
       │    │  audit_logs  │
       │    │──────────────│
       │    │ id (PK)      │
       │    │ actor_id     │ (FK→users)
       │    │ actor_role   │
       │    │ action       │
       │    │ action_type  │
       │    │ ip_address   │
       │    │ user_agent   │
       │    │ details      │
       │    │ created_at/  │
       │    │ updated_at   │
       │    └──────────────┘
```

## Table Summary

| Table | Rows (approx) | Purpose |
|-------|---------------|---------|
| branches | 2 | Cabang (JKT, BTM) |
| users | 2 | Admin + Management_View |
| customers | 3+ | Data pelanggan (perusahaan/individual) |
| shipments | 12+ | Data resi pengiriman |
| tracking_events | 48+ | Timeline tracking per resi |
| manifests | 4+ | Manifest antar cabang |
| manifest_items | 10+ | Pivot manifest ↔ shipment |
| invoices | 1+ | Invoice konsolidasi |
| invoice_items | 3+ | Pivot invoice ↔ shipment |
| invoice_payments | 0+ | Pembayaran parsial invoice |
| regions | 88,296 | Data wilayah Indonesia (provinsi → desa) |
| packages | 0+ | Detail koli per resi (optional) |
| audit_logs | 100+ | Audit trail semua CRUD |
| cache/cache_locks | runtime | File cache (Laravel) |
| sessions | runtime | Session storage (file) |
| jobs/failed_jobs/job_batches | runtime | Queue (unused, sync mode) |
| personal_access_tokens | runtime | Sanctum API tokens |
| password_reset_tokens | runtime | Password reset |
| migrations | 20+ | Migration history |

## Key Relationships

1. **branches** → users (1:N, branch_id)
2. **branches** → shipments (1:N, origin_branch_id + destination_branch_id)
3. **branches** → manifests (1:N, origin_branch_id + destination_branch_id)
4. **branches** → invoices (1:N, branch_id)
5. **customers** → shipments (1:N, customer_id)
6. **customers** → invoices (1:N, customer_id)
7. **shipments** → tracking_events (1:N, shipment_id)
8. **shipments** ↔ manifests (M:N via manifest_items)
9. **shipments** ↔ invoices (M:N via invoice_items)
10. **invoices** → invoice_payments (1:N, invoice_id)
11. **users** → tracking_events (1:N, actor_id)
12. **users** → audit_logs (1:N, actor_id)
13. **regions** → self (parent_code, 4 levels: province → regency → district → village)

## Updated (2026-11-07)
- purchase_orders table RECREATED (migration 2026_11_07_000001)
  - Columns: id (UUID), po_number (unique), customer_id (FK), total_amount, paid_amount, status (draft/active/paid/partial/cancelled), notes, timestamps
- invoices.po_id (UUID FK → purchase_orders.id) — re-added, auto-linked from shipment po_number
- invoices.parent_invoice_id (UUID FK → invoices.id, nullable) — for partial payment split chain
- shipments.po_number (string) — links shipment to PO by number
- PO auto-created when shipment created with po_number + customer_id
- PO total set from invoice total when invoice created (only if still 0)
MD
echo "done"