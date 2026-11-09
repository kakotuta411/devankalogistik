#!/bin/bash
# Setup Redis + OPcache optimization for production

echo "========================================"
echo "  REDIS + OPCACHE SETUP"
echo "  $(date)"
echo "========================================"
echo ""

# ============================================
# 1. Install Redis Server
# ============================================
echo "1. Installing Redis..."
sudo apt update -qq
sudo apt install -y redis-server php-redis

# Configure Redis (bind localhost only, no password needed for local)
sudo sed -i 's/^bind .*/bind 127.0.0.1/' /etc/redis/redis.conf
sudo sed -i 's/^# maxmemory .*/maxmemory 64mb/' /etc/redis/redis.conf
sudo sed -i 's/^# maxmemory-policy .*/maxmemory-policy allkeys-lru/' /etc/redis/redis.conf

sudo systemctl enable redis-server
sudo systemctl restart redis-server

# Verify
if redis-cli ping 2>/dev/null | grep -q "PONG"; then
    echo "   ✅ Redis running (PONG)"
else
    echo "   ❌ Redis failed to start"
fi
php -r "echo '   PHP Redis ext: ' . (extension_loaded('redis') ? 'YES' : 'NO') . PHP_EOL;"
echo ""

# ============================================
# 2. OPcache Production Optimization
# ============================================
echo "2. OPcache Production Tuning..."
OPCACHE_INI=$(php -r "echo php_ini_loaded_file();")
echo "   PHP ini: $OPCACHE_INI"

# Find opcache config
OPCACHE_CONF=$(find /etc/php -name "opcache.ini" 2>/dev/null | head -1)
if [ -z "$OPCACHE_CONF" ]; then
    OPCACHE_CONF="/etc/php/8.3/cli/conf.d/10-opcache.ini"
fi
echo "   OPcache config: $OPCACHE_CONF"

# Create/update opcache config
sudo tee "$OPCACHE_CONF" > /dev/null << 'EOF'
; OPcache production settings
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=60
opcache.validate_timestamps=0
opcache.save_comments=1
opcache.fast_shutdown=1
EOF

# Also set for FPM
FPM_OPCACHE=$(find /etc/php -path "*fpm*" -name "*opcache*" 2>/dev/null | head -1)
if [ -n "$FPM_OPCACHE" ]; then
    sudo cp "$OPCACHE_CONF" "$FPM_OPCACHE"
fi

echo "   ✅ OPcache tuned (validate_timestamps=0 for prod)"
echo "   ⚠️  Note: After code changes, run: sudo systemctl restart php8.3-fpm"
echo ""

# ============================================
# 3. Update Laravel .env for Redis
# ============================================
echo "3. Updating .env for Redis..."
cd /var/www/devanka-logistik

# Backup .env
cp .env .env.backup.$(date +%Y%m%d)

# Update cache/session/queue drivers
sed -i 's/^CACHE_DRIVER=.*/CACHE_DRIVER=redis/' .env
sed -i 's/^SESSION_DRIVER=.*/SESSION_DRIVER=redis/' .env
sed -i 's/^QUEUE_CONNECTION=.*/QUEUE_CONNECTION=redis/' .env

# Add Redis config if not exists
grep -q "^REDIS_HOST=" .env || echo "REDIS_HOST=127.0.0.1" >> .env
grep -q "^REDIS_PASSWORD=" .env || echo "REDIS_PASSWORD=null" >> .env
grep -q "^REDIS_PORT=" .env || echo "REDIS_PORT=6379" >> .env

echo "   ✅ .env updated (CACHE/SESSION/QUEUE = redis)"
echo ""

# ============================================
# 4. Clear & Rebuild Cache
# ============================================
echo "4. Rebuilding Laravel cache..."
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan cache:clear
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
sudo -u www-data php artisan optimize
echo "   ✅ Cache rebuilt"
echo ""

# ============================================
# 5. Restart PHP-FPM (load new OPcache + Redis ext)
# ============================================
echo "5. Restarting PHP-FPM..."
sudo systemctl restart php8.3-fpm
echo "   ✅ PHP-FPM restarted"
echo ""

# ============================================
# Summary
# ============================================
echo "========================================"
echo "  SETUP COMPLETE"
echo "========================================"
echo ""
echo "Installed:"
echo "  - Redis server (maxmemory 64MB, allkeys-lru)"
echo "  - PHP Redis extension"
echo "  - OPcache (128M, validate_timestamps=0)"
echo ""
echo "Laravel .env:"
echo "  - CACHE_DRIVER=redis"
echo "  - SESSION_DRIVER=redis"
echo "  - QUEUE_CONNECTION=redis"
echo ""
echo "⚠️  IMPORTANT:"
echo "  - After code deploy, run: sudo systemctl restart php8.3-fpm"
echo "  - OPcache validate_timestamps=0 means code changes need FPM restart"
echo "  - Redis maxmemory 64MB (adjust if needed)"
echo "  - .env backup saved as .env.backup.YYYYMMDD"
echo ""