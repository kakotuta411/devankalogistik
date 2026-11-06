# Environment Configuration

## .env (Production/Shared Hosting)
```
APP_NAME=AKL Logistik
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=logistik_min
DB_USERNAME=logistik
DB_PASSWORD=***

CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=public

DB_TIMEZONE=Asia/Jakarta
APP_TIMEZONE=Asia/Jakarta
```

## .env (Docker Dev)
```
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=logistik_min
DB_USERNAME=logistik
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
