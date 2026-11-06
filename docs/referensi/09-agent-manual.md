# Agent Manual — For New AI Agent

## Project Location
- Dev: /home/ambon/akl-logistik-hosting
- Original (PostgreSQL): /home/ambon/akllogistik-minimal (untouched)

## Quick Start
```bash
cd /home/ambon/akl-logistik-hosting
docker compose up -d
docker exec lt-hosting-app php artisan migrate --force
docker exec lt-hosting-app php artisan db:seed --force
# Access: http://localhost:8080
# Login: admin@logistik.test / password
```

## Key Files
- Routes: routes/web.php, routes/api.php
- Controllers: app/Http/Controllers/
- Models: app/Models/
- Views: resources/views/
- Migrations: database/migrations/
- Seeders: database/seeders/DummyDataSeeder.php

## Important Rules
1. NO Filament, NO Case Management
2. All FK fields use Select not TextInput UUID
3. BranchScope in booted() of each scoped model
4. Sanctum: tokenable_id must be uuid if User PK is UUID
5. User PK is UUID (HasUuid trait)
6. Pagination default: Bootstrap 5 (AppServiceProvider)
7. API routes prefixed with api. namespace

## Common Commands
```bash
# Migrate
docker exec lt-hosting-app php artisan migrate --force

# Seed
docker exec lt-hosting-app php artisan db:seed --force

# Seed regions (needs internet, ~10 min)
docker exec lt-hosting-app php artisan regions:sync

# Clear cache
docker exec lt-hosting-app php artisan cache:clear && php artisan view:clear

# Check logs
docker exec lt-hosting-app tail -20 storage/logs/laravel.log

# Restart
docker compose restart app nginx
```

## Key Patterns
- Modal edit: AJAX form extraction (fetch edit URL → extract form → append to modal body)
- Region cascading: provinsi → kab/kota → kecamatan → kelurahan (JS loadOptions)
- Customer search: dropdown select, auto-fill sender fields + region
- Invoice costs: all manual (no auto-rate calculation)
- Partial payment: recordPayment + progress bar + history

## Documentation
- All docs in: docs/referensi/
- Checkpoint: docs/referensi/07-checkpoint.md
- API: docs/referensi/04-api-endpoints.md
- DB: docs/referensi/03-database.md
