# GitHub Deploy & CI/CD Setup

## GitHub Secrets (Settings → Secrets and variables → Actions)
| Secret | Deskripsi |
|--------|-----------|
| `DEPLOY_HOST` | IP/domain server hosting |
| `DEPLOY_USER` | SSH username |
| `DEPLOY_SSH_KEY` | Private SSH key (paste full key) |
| `DEPLOY_PORT` | SSH port (default 22) |
| `DEPLOY_PATH` | Path project di server (e.g. /home/user/public_html) |

## CI Pipeline (.github/workflows/ci.yml)
- Trigger: push/PR ke master atau main
- Steps: setup PHP 8.3, install composer, migrate (MariaDB service), run tests, lint PHP
- MariaDB 11 service container dengan DB logistik_test

## CD Pipeline (.github/workflows/deploy.yml)
- Trigger: push ke master atau manual (workflow_dispatch)
- Steps: checkout, setup PHP, composer install --no-dev, SSH deploy
- SSH deploy: git pull, composer install, migrate --force, cache config/route/view, optimize

## Setup Awal di Server Hosting
1. SSH ke server
2. Clone repo: `git clone https://github.com/USER/REPO.git /path/to/project`
3. Copy .env.example → .env, isi credentials
4. `composer install --no-dev --optimize-autoloader`
5. `php artisan key:generate`
6. `php artisan migrate --force`
7. `php artisan db:seed --force`
8. `php artisan storage:link`
9. Set permissions: `chmod -R 775 storage bootstrap/cache`

## Catatan
- Shared hosting: pastikan PHP 8.3+ tersedia
- Extension yang dibutuhkan: pdo_mysql, mbstring, xml, curl, zip, gd, bcmath
- MariaDB 11 atau MySQL 8+
- Region sync butuh internet (~10 min, 88k rows)
- Logo cache-busting via filemtime query param