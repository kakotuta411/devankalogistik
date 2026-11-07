# Devanka Logistik — Overview

## Project Info
- **Name**: devanka-logistik (shared hosting / VPS version)
- **Framework**: Laravel 13 (PHP 8.3)
- **Database**: MariaDB 11
- **Cache**: File (storage/framework/cache/data)
- **Session**: File (storage/framework/sessions)
- **Queue**: Sync (no worker needed)
- **Storage**: Local disk (storage/app/public)
- **UI**: Bootstrap 5.3.3 (local, no CDN)
- **Print**: domPDF (barryvdh/laravel-dompdf)
- **Barcode**: Code 128 via GD (app/Helpers/BarcodeGenerator.php)

## Scope
Simplified logistics tracking system. NO Filament, NO Case Management.
Core features: Shipment tracking, Manifest antar cabang, Branch, Region sync, Tracking Events, Audit Trail, User Management RBAC, Print templates, Invoice management (consolidated), Customer management, PO tracking, Partial payment, Public tracking API.

## User Roles
- Admin (super admin, full access)
- Management_View (read-only, sees all data, no branch scope)
- Staff_Invoice (CRUD invoices + customers + shipments + manifests + PO, branch scoped)
- Staff_Shipment (CRUD shipments + manifests, view invoices, branch scoped)

## Branches (2)
- JKT (Jakarta Hub)
- BTM (Batam Hub)

## Users (2)
- admin@logistik.test (Admin)
- user4@logistik.test (Management_View)

## Prefixes
- Resi: AKL-{YYYYMMDD}-{SEQ:5}
- Manifest: AKL-MNF-{YYYYMMDD}-{SEQ:5}
- Invoice: AKL-INV-{YYYYMM}-{SEQ:5}
- PO: auto-generated from shipment po_number

## Shipping Rates
- Costs now manual (no auto-rate calculation)
- SHIPPING_RATE_REGULAR / EXPRESS deprecated

## Payment Types
- kredit (credit)
- lunas (cash/paid)
- COD (cash on delivery)

## Invoice Calculation Formula
- total_layanan = (shipping_cost × weight) + packaging_cost + equipment_charge + other_costs
- total_amount = total_layanan - discount + ppftz03 + ppn + pph
- discount = percentage of total_layanan

## Print Types
1. Koli label: 10×10cm thermal, Code 128 barcode, multi-page (1 per koli)
2. Resi detail: half-A4 (9.5×5.5in landscape), absolute positioning, for customer handover

## Public Tracking (limited PII)
- resi_no, entry_date (with time), sender, receiver, status, timeline
- receiver_name & delivered_at (only if completed)

## PO Flow
- Shipment (po_number) → auto-create PO (total=0)
- Invoice (linked via auto-detect po_id from shipment)
- Payment (full/partial) → parent status = Paid, child invoice for remainder
- PO status sync: draft → active → paid/partial
- PO paid_amount = sum amount_paid all invoices
- If PO has shipment in active (non-cancelled) invoice → exclude entire PO from selection