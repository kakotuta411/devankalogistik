# Agent Manual — For New AI Agent

## Project Location
- Dev: /home/ambon/devanka-logistik
- Original (PostgreSQL): /home/ambon/akllogistik-minimal (untouched)
- VPS: /var/www/devanka-logistik (Ubuntu 24.04, PHP 8.3, MariaDB 11, Nginx)

## Quick Start (Docker Dev)
```bash
cd /home/ambon/devanka-logistik
docker compose up -d
docker exec lt-hosting-app php artisan migrate --force
docker exec lt-hosting-app php artisan db:seed --force
# Access: http://localhost:8080
# Login: admin@logistik.test / password
```

## Quick Start (VPS Production)
```bash
cd /var/www/devanka-logistik
git pull origin master
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:clear && php artisan cache:clear
sudo systemctl restart php8.3-fpm
# Access: http://devakalogistik.web.id (43.173.50.217)
```

## Key Files
- Routes: routes/web.php, routes/api.php
- Controllers: app/Http/Controllers/ (Shipment, Manifest, Invoice, Customer, PurchaseOrder, Print, Dashboard, Api/Tracking)
- Models: app/Models/ (14 active)
- Views: resources/views/
- Migrations: database/migrations/
- Seeders: database/seeders/
- Helpers: app/Helpers/BarcodeGenerator.php

## Important Rules
1. NO Filament, NO Case Management
2. All FK fields use Select not TextInput UUID
3. BranchScope in booted() of each scoped model
4. Sanctum: tokenable_id must be uuid if User PK is UUID
5. User PK is UUID (HasUuid trait)
6. Pagination default: Bootstrap 5 (AppServiceProvider)
7. API routes prefixed with api. namespace
8. DB_TIMEZONE=+07:00 (avoid MySQL timezone table issues)
9. storage/framework/{sessions,views,cache/data} must exist (gitignored, create manually on fresh deploy)

## Common Commands
```bash
# Migrate
php artisan migrate --force

# Seed
php artisan db:seed --force
php artisan db:seed --class=RegionSeeder --force

# Clear cache
php artisan config:clear && php artisan cache:clear

# Check logs
tail -20 storage/logs/laravel.log

# Restart PHP-FPM
sudo systemctl restart php8.3-fpm

# Permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

## Key Patterns
- Modal edit: AJAX form extraction (fetch edit URL → extract form → append to modal body)
- Region cascading: provinsi → kab/kota → kecamatan → kelurahan (JS loadOptions)
- Customer search: dropdown select, auto-fill sender fields + region
- Invoice costs: all manual (no auto-rate calculation)
- Partial payment: recordPayment + progress bar + history
- PO auto-create on shipment store (total=0)
- Invoice-PO auto-link: auto-detect po_id from selected shipments' po_number
- Partial payment split: parent status = Paid, child invoice for remainder, parent locked
- PO status sync: draft → active → paid/partial
- Code 128 barcode via GD (app/Helpers/BarcodeGenerator.php)

## Documentation
- All docs in: referensi/
- Checkpoint: referensi/07-checkpoint.md
- API: referensi/04-api-endpoints.md
- DB: referensi/03-database.md
- RBAC: referensi/11-rbac-breakdown.md
- Tracking API: referensi/12-tracking-api.md
- Tracking Page: referensi/13-tracking-page.html
- Deploy CI/CD: referensi/14-deploy-github-cicd.md
- Deploy VPS: referensi/08-production-deploy.md
- DB Schema ERD: referensi/10-database-schema.md

## CI/CD
- GitHub: kakotuta411/devankalogistik (private)
- CI: .github/workflows/ci.yml — test on push/PR
- CD: .github/workflows/deploy.yml — SSH deploy on push to master
- Secrets: DEPLOY_HOST, DEPLOY_USER, DEPLOY_SSH_KEY, DEPLOY_PORT, DEPLOY_PATH
- VPS IP: 43.173.50.217
- Domain: devakalogistik.web.id

## Deploy History (VPS)
- Initial deploy: clone, composer install, migrate, seed
- Issues encountered:
  1. Migration cleanup_unused FK drop error → removed migration file
  2. RegionSeeder duplicate entry → drop & recreate DB
  3. DummyDataSeeder JKT/BTM not found → create branches/users via tinker first
  4. MySQL timezone unknown → use DB_TIMEZONE=+07:00
  5. Permission denied on storage → chown www-data
  6. Missing storage/framework/{sessions,views,cache} → mkdir manually
  7. CI test 302 vs 200 → fix example test to GET /login
- Final: site accessible via IP and hostname, login working