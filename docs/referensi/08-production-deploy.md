# Production Deployment Guide

## Prerequisites
- PHP 8.3+ with extensions: pdo_mysql, mbstring, xml, curl, zip, gd, bcmath
- MariaDB 11 (or MySQL 8+)
- Composer
- Nginx (or Apache with mod_rewrite)
- Git (optional, for updates)

## Migration Notes (from akllogistik-minimal)

If migrating from the original PostgreSQL version:
- Database: PostgreSQL 15 → MariaDB 11 (migrations already adapted)
- Cache: Redis 7 → File cache (no Redis needed)
- Storage: MinIO/S3 → Local disk (no MinIO needed)
- Queue: Sync (no worker needed, same as before)
- Docker: Full stack → MySQL + PHP-FPM + Nginx (dev only)

Migration fixes already applied:
- jsonb → json (audit_logs)
- Removed CHECK constraints (MySQL compatibility)
- Removed duplicate seal_number column
- enum type retained (supported in both)

## Step 1: Clone/Copy Project
```bash
git clone <repo-url> /var/www/akl-logistik
cd /var/www/akl-logistik
```
Or copy from dev: `scp -r /home/ambon/akl-logistik-hosting user@vps:/var/www/akl-logistik`

## Step 2: Install Dependencies
```bash
composer install --no-dev --optimize-autoloader
```

## Step 3: Configure Environment
```bash
cp .env.example .env
php artisan key:generate
```
Edit `.env`:
```
APP_NAME="AKL Logistik"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=logistik_min
DB_USERNAME=logistik_user
DB_PASSWORD=your_secure_password

CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=public

DB_TIMEZONE=Asia/Jakarta
```

## Step 4: Database Setup
```sql
CREATE DATABASE logistik_min CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'logistik_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON logistik_min.* TO 'logistik_user'@'localhost';
FLUSH PRIVILEGES;
```

## Step 5: Run Migrations & Seed
```bash
php artisan migrate --force
php artisan db:seed --force  # Seeds branches, users, dummy data
php artisan regions:sync     # Sync 88,296 regions (needs internet, ~10 min)
```

## Step 6: Storage Setup
```bash
php artisan storage:link
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## Step 7: Nginx Config
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/akl-logistik/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```
Enable: `ln -s /etc/nginx/sites-available/akl /etc/nginx/sites-enabled/ && nginx -t && systemctl reload nginx`

## Step 8: SSL (Let's Encrypt)
```bash
certbot --nginx -d your-domain.com
```

## Step 9: Cron (Region Sync Daily)
```bash
crontab -e
# Add:
0 2 * * * cd /var/www/akl-logistik && php artisan regions:sync >> /dev/null 2>&1
```

## Step 10: Verify
```bash
php artisan about
curl -s https://your-domain.com/login | grep -o "AKL"
```

## Default Login
- Email: admin@logistik.test
- Password: password (CHANGE IMMEDIATELY)

## Maintenance
- Backup DB: `mysqldump -u logistik_user -p logistik_min > backup_$(date +%Y%m%d).sql`
- Clear cache: `php artisan cache:clear && php artisan view:clear`
- Update code: `git pull && composer install --no-dev && php artisan migrate --force`

## Docker Dev (Local)
```bash
cd /home/ambon/akl-logistik-hosting
docker compose up -d
docker exec lt-hosting-app php artisan migrate --force
docker exec lt-hosting-app php artisan db:seed --force
```
Access: http://localhost:8080
