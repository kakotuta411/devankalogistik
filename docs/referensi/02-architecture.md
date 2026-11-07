# Architecture & Infrastructure

## Docker (Dev Only)
- MariaDB 11 (port 3306, bind mount ./mysql_data)
- PHP-FPM 8.3 (port 9000 internal)
- Nginx (port 8080)

## Production (Shared Hosting)
- Native PHP 8.3 + MariaDB 11
- No Docker, no Redis, no MinIO, no queue worker
- File cache + file session + local disk storage

## Cloudflare Tunnels (3 active)
- akl-logistik-hosting → 43.173.50.217 (devakalogistik.web.id)
- Matupa Coffee → localhost:80
- PromotorOps → localhost:8081

## Key Middleware
- `auth` — authentication required
- `readonly` — blocks POST/PUT/PATCH/DELETE for Executive_Viewer
- `role:Admin,Head_of_Branch` — branch management
- `role:Admin` — user management, template management

## BranchScope
Applied per-model to restrict data by branch (non-Admin users see own branch data).
Executive_Viewer bypasses scope (sees all data, read-only).

## Region Sync
- Source: emsifa.com API
- Command: `php artisan regions:sync`
- Cron: daily 02:00
- Data: 35 provinces, 514 regencies, 7,215 districts, 80,532 villages

## Print Templates
- Settings stored in: storage/app/public/print_settings.json
- Per doc type: invoice, manifest, shipment
- Logo converted to base64 for domPDF offline rendering
- Paper formats: A4, Letter, thermal (10x10cm), dotmatrix-half (241x140mm), A5 (half-A4)
