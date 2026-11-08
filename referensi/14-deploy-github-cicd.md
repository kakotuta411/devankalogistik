# GitHub Deploy & CI/CD Setup

## Repo Info
- GitHub: kakotuta411/devankalogistik (private)
- Branch: master
- CI/CD: GitHub Actions

## GitHub Secrets (Settings → Secrets and variables → Actions)
| Secret | Value |
|--------|-------|
| `DEPLOY_HOST` | 43.173.50.217 |
| `DEPLOY_USER` | ubuntu (or SSH user) |
| `DEPLOY_SSH_KEY` | Private SSH key (full key content) |
| `DEPLOY_PORT` | 22 |
| `DEPLOY_PATH` | /var/www/devanka-logistik |

## CI Pipeline (.github/workflows/ci.yml)
- Trigger: push/PR ke master atau main
- Steps: setup PHP 8.3, install composer, migrate (MariaDB service), run tests, lint PHP
- MariaDB 11 service container dengan DB logistik_test
- Note: ExampleTest uses GET /login (not /) due to auth redirect

## CD Pipeline (.github/workflows/deploy.yml)
- Trigger: push ke master atau manual (workflow_dispatch)
- Steps: checkout, setup PHP, composer install --no-dev, SSH deploy
- SSH deploy: git pull, composer install, migrate --force, cache config/route/view, optimize
- Auto-deploy active (secrets configured)

## Setup Awal di VPS
1. SSH ke server: `ssh ubuntu@43.173.50.217`
2. Clone repo: `git clone https://github.com/kakotuta411/devankalogistik.git /var/www/devanka-logistik`
3. Copy .env.example → .env, isi credentials
4. `composer install --no-dev --optimize-autoloader`
5. `php artisan key:generate`
6. Create storage dirs: `mkdir -p storage/framework/{sessions,views,cache/data}`
7. `php artisan migrate --force`
8. Create branches & users via tinker
9. `php artisan db:seed --class=RegionSeeder --force`
10. `php artisan storage:link`
11. Set permissions: `sudo chown -R www-data:www-data storage bootstrap/cache`
12. Configure Nginx (see 08-production-deploy.md)
13. Reload Nginx

## Catatan
- VPS: Ubuntu 24.04, PHP 8.3, MariaDB 11, Nginx
- Extension yang dibutuhkan: pdo_mysql, mbstring, xml, curl, zip, gd, bcmath
- Region sync butuh internet (~10 min, 88k rows)
- Logo cache-busting via filemtime query param
- storage/framework/{sessions,views,cache/data} must exist (gitignored)
- DB_TIMEZONE=+07:00 (avoid MySQL timezone table issues)
- Docker for dev only; production uses native PHP + Nginx + MariaDB

## Deploy History
- Initial deploy: clone, composer install, migrate, seed
- Issues: FK drop, duplicate region, branch not found, timezone, permissions, missing dirs, CI test 302
- All resolved — site live at devankalogistik.web.id
- Auto-deploy via GitHub Actions active