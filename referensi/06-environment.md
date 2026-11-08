# Environment Configuration

## .env (Production/VPS)
```
APP_NAME=Devanka Logistik
APP_ENV=production
APP_DEBUG=false
APP_URL=https://devankalogistik.web.id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=devanka_logistik
DB_USERNAME=devanka
DB_PASSWORD=***
DB_TIMEZONE=+07:00

CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=public

APP_TIMEZONE=Asia/Jakarta
```

## .env (Docker Dev)
```
APP_NAME=Devanka Logistik
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8080

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=devanka_logistik
DB_USERNAME=devanka
DB_PASSWORD=secret

CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=public

SHIPPING_RATE_REGULAR=10000
SHIPPING_RATE_EXPRESS=15000
```

## Docker Compose (Dev)
- MariaDB 11 (port 3306)
- PHP-FPM 8.3 (internal 9000)
- Nginx (port 8080)
- Bind mount: ./mysql_data:/var/lib/mysql

## Storage Paths
- Cache: storage/framework/cache/data
- Sessions: storage/framework/sessions
- Views: storage/framework/views
- Logs: storage/logs/laravel.log
- Public: storage/app/public (POD photos, logos)
- Print settings: storage/app/public/print_settings.json

## Key Config
- Pagination: Bootstrap 5 (AppServiceProvider)
- Timezone: Asia/Jakarta (config/app.php + config/database.php)
- Trust proxies: * (for Cloudflare tunnel HTTPS detection)
- CORS: enabled (Access-Control-Allow-Origin: *) for public tracking API

## VPS (Production)
- OS: Ubuntu 24.04
- PHP: 8.3 (FPM)
- MariaDB: 11
- Nginx: latest
- IP: 43.173.50.217
- Domain: devankalogistik.web.id
- App path: /var/www/devanka-logistik
- DB: devanka_logistik / user: devanka
- Web server user: www-data

## Important Notes
- storage/framework/{sessions,views,cache/data} must exist (gitignored, create manually on fresh deploy)
- DB_TIMEZONE=+07:00 (avoid MySQL timezone table issues)
- No Docker in production (native PHP + Nginx + MariaDB)