# Production Deployment Guide (VPS)

## Server Info
- OS: Ubuntu 24.04
- PHP: 8.3 (FPM)
- MariaDB: 11
- Nginx: latest
- IP: 43.173.50.217
- Domain: devankalogistik.web.id
- App path: /var/www/devanka-logistik
- Web server user: www-data

## Prerequisites
- PHP 8.3+ with extensions: pdo_mysql, mbstring, xml, curl, zip, gd, bcmath
- MariaDB 11 (or MySQL 8+)
- Composer
- Nginx
- Git

## Step 1: Clone Project
```bash
sudo mkdir -p /var/www && sudo chown ubuntu:ubuntu /var/www
git clone https://github.com/kakotuta411/devankalogistik.git /var/www/devanka-logistik
cd /var/www/devanka-logistik
```

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
APP_NAME="Devanka Logistik"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://devankalogistik.web.id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=devanka_logistik
DB_USERNAME=devanka
DB_PASSWORD=your_secure_password
DB_TIMEZONE=+07:00

CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=public

APP_TIMEZONE=Asia/Jakarta
```

## Step 4: Database Setup
```sql
CREATE DATABASE devanka_logistik CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'devanka'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON devanka_logistik.* TO 'devanka'@'localhost';
FLUSH PRIVILEGES;
```

## Step 5: Create Storage Directories (IMPORTANT)
```bash
mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

## Step 6: Run Migrations & Seed
```bash
php artisan migrate --force
# Create branches & users first (via tinker or seeder)
php artisan db:seed --class=RegionSeeder --force  # ~88k rows, needs internet, ~10 min
php artisan storage:link
```

**Important**: Create branches (JKT, BTM) and users before running DummyDataSeeder.

## Step 7: Nginx Config
```nginx
server {
    listen 80;
    server_name devankalogistik.web.id 43.173.50.217;
    root /var/www/devanka-logistik/public;
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
Enable:
```bash
sudo ln -s /etc/nginx/sites-available/devanka-logistik /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t && sudo systemctl reload nginx
```

## Step 8: SSL (Let's Encrypt) — Optional
```bash
sudo certbot --nginx -d devankalogistik.web.id
```

## Step 9: Cron (Region Sync Daily)
```bash
crontab -e
# Add:
0 2 * * * cd /var/www/devanka-logistik && php artisan regions:sync >> /dev/null 2>&1
```

## Step 10: Verify
```bash
php artisan about
curl -s http://43.173.50.217/login | grep -o "Devanka"
```

## Default Login
- Email: admin@logistik.test
- Password: password (CHANGE IMMEDIATELY)

## Maintenance
- Backup DB: `mysqldump -u devanka -p devanka_logistik > backup_$(date +%Y%m%d).sql`
- Clear cache: `php artisan config:clear && php artisan cache:clear`
- Update code: `git pull && composer install --no-dev && php artisan migrate --force && php artisan config:clear && sudo systemctl restart php8.3-fpm`
- Check logs: `tail -20 storage/logs/laravel.log`

## Troubleshooting

### Migration cleanup_unused FK error
```
Can't DROP FOREIGN KEY `invoices_po_id_foreign`
```
**Fix**: Remove migration file `2026_11_06_000001_cleanup_unused.php` entirely.

### RegionSeeder duplicate entry
```
Duplicate entry '11' for key 'PRIMARY'
```
**Fix**: Drop & recreate DB, then re-migrate + seed.

### DummyDataSeeder branch not found
```
ERROR: JKT or BTM branch not found. Run base seeder first.
```
**Fix**: Create branches (JKT, BTM) and users via tinker before running DummyDataSeeder.

### MySQL timezone unknown
```
Unknown or incorrect time zone
```
**Fix**: Use `DB_TIMEZONE=+07:00` in .env (not `Asia/Jakarta`).

### Permission denied on storage
```
Failed to clear cache. Make sure you have the appropriate permissions.
```
**Fix**: `sudo chown -R www-data:www-data storage bootstrap/cache && sudo chmod -R 775 storage bootstrap/cache`

### Missing storage/framework directories
```
Failed to open stream: No such file or directory (sessions/views/cache)
```
**Fix**: `mkdir -p storage/framework/{sessions,views,cache/data}`

### CI test 302 vs 200
```
Expected response status code [200] but received 302.
```
**Fix**: Update ExampleTest to GET /login instead of / (auth redirect is normal).

### Login failed
**Fix**: Reset password via tinker: `$u->password = bcrypt('password'); $u->save();`

### Nginx not configured / default page
**Fix**: Create site config, enable symlink, remove default, reload Nginx.

### Connection timed out
**Fix**: Ensure Nginx listening on port 80, check firewall (ufw allow 80).

## Docker Dev (Local)
```bash
cd /home/ambon/devanka-logistik
docker compose up -d
docker exec lt-hosting-app php artisan migrate --force
docker exec lt-hosting-app php artisan db:seed --force
```
Access: http://localhost:8080