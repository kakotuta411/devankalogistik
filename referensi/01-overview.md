# AKL Logistik — Overview

## Project Info
- **Name**: akl-logistik-hosting (shared hosting version)
- **Framework**: Laravel 13 (PHP 8.3)
- **Database**: MariaDB 11
- **Cache**: File (storage/framework/cache/data)
- **Session**: File (storage/framework/sessions)
- **Queue**: Sync (no worker needed)
- **Storage**: Local disk (storage/app/public)
- **UI**: Bootstrap 5.3.3 (local, no CDN)
- **Print**: domPDF (barryvdh/laravel-dompdf)
- **QR Code**: chillerlan/php-qrcode

## Scope
Simplified logistics tracking system. NO Filament, NO Case Management.
Core features: Shipment tracking, Manifest antar cabang, Branch, Region sync, Tracking Events, Audit Trail, User Management RBAC, Print templates, Invoice management (consolidated), Customer management, PO tracking, Partial payment.

## User Roles
- Admin (super admin, full access)
- Head_of_Branch
- Leader_Supervisor
- Executive_Viewer (read-only, sees all data)
- Staf

## Branches (2)
- JKT (Jakarta Hub)
- BTM (Batam Hub)

## Users (2)
- admin@logistik.test (Admin)
- user4@logistik.test (Executive_Viewer/direktur)

## Prefixes
- Resi: AKL-{YYYYMMDD}-{SEQ:5}
- Manifest: AKL-MNF-{YYYYMMDD}-{SEQ:5}
- Invoice: AKL-INV-{YYYYMM}-{SEQ:5}

## Shipping Rates
- SHIPPING_RATE_REGULAR=10000 (per kg) — deprecated, costs now manual
- SHIPPING_RATE_EXPRESS=15000 (per kg) — deprecated, costs now manual
