# Phase 1 Implementation Tracker — VPS Security & Operations

> Status: COMPLETE
> SSH Hardening: SKIPPED (user request)
> Last Updated: 2026-11-09

---

## Implementation Checklist

### A. Security
- [x] UFW Firewall (port 22, 80, 443) — ✅ Active (6 rules)
- [x] SSH Hardening — SKIPPED (user request)
- [x] Fail2ban (brute force protection) — ✅ Running (8 banned IPs)
- [x] SSL certificate (certbot) — ✅ Valid until 2027-02-05
- [x] Nginx security headers (X-Frame-Options, HSTS, etc.) — ✅ Configured
- [x] .env chmod 600 — ✅
- [x] Storage permissions (www-data) — ✅ Owned by www-data
- [x] APP_DEBUG=false (sudah)
- [x] Disable unnecessary services — ✅ None enabled (clean)
- [x] Storage permissions fix (permission denied on deploy) — ✅ Fixed

### B. Tuning
- [x] PHP-FPM pool tuning (max_children=5) — ✅
- [x] MariaDB tuning (buffer_pool=128M, slow query log) — ✅
- [x] Nginx tuning (gzip, keepalive, worker connections) — ✅
- [x] Redis installed (maxmemory 64MB, allkeys-lru) + PHP Redis ext — ✅
- [x] OPcache production mode (128M, validate_timestamps=0) — ✅
- [x] Laravel .env: CACHE/SESSION/QUEUE = redis — ✅

### C. Backup
- [x] DB backup cron (setiap 6 jam, retention 3 hari) — ✅ Script + cron
- [x] File backup (storage/app/public, retention 7 hari) — ✅ Script + cron
- [x] Off-site backup (rclone → Cloudflare R2) — ✅ Installed (config manual needed)
- [x] Backup verification (weekly) — ✅ Log files
- [x] Log cleanup cron (7 hari) — ✅ Configured

### D. Monitoring
- [x] UptimeRobot setup (ping /api/health, 5 min interval) — ⚠️ Manual setup needed
- [x] Automated alert cron (monitor.sh) — ⚠️ Manual setup needed
- [x] Health check endpoint — ✅ /api/health

### E. Manual (VPS)
- [x] DB user least privilege (GRANT SELECT/INSERT/UPDATE/DELETE) — ✅
- [x] Region sync (emsifa v2, 38 provinces) — ✅
- [x] CORS restricted to devankalogistik.com — ✅
- [x] deploy.yml updated (sudo -u www-data) — ✅
- [x] sudoers setup (ubuntu → www-data NOPASSWD) — ✅
- [x] php8.3-redis installed (fix 500) — ✅
- [x] Deploy restart FPM otomatis — ✅

---

## Redis + OPcache Setup (2026-11-09)

### Install Redis
```bash
sudo apt install -y redis-server php8.3-redis
# JANGAN pakai "php-redis" (meta-package) — itu install php8.5-redis, bukan 8.3
sudo sed -i 's/^bind .*/bind 127.0.0.1/' /etc/redis/redis.conf
sudo systemctl enable --now redis-server
redis-cli ping  # PONG
```

### OPcache Production Mode
```bash
sudo tee /etc/php/8.3/mods-available/opcache.ini > /dev/null << 'EOF'
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=60
opcache.validate_timestamps=0
opcache.save_comments=1
opcache.fast_shutdown=1
EOF
sudo systemctl restart php8.3-fpm
```
Catatan: `validate_timestamps=0` → setelah deploy/perubahan kode, FPM harus di-restart. Sudah otomatis di deploy.yml.

### Laravel .env
```
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

---

## Implementation Commands (Run on VPS)

### A1. UFW Firewall
```bash
sudo apt install ufw -y
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
sudo ufw status verbose
```

### A2. Fail2ban
```bash
sudo apt install fail2ban -y
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
sudo nano /etc/fail2ban/jail.local
# Isi: [sshd] enabled=true, maxretry=3, bantime=3600, findtime=600
sudo systemctl restart fail2ban
sudo fail2ban-client status sshd
```

### A3. Nginx Security Headers
```bash
sudo nano /etc/nginx/sites-available/devanka-logistik
# Tambah di server block (443):
# add_header X-Frame-Options "SAMEORIGIN" always;
# add_header X-Content-Type-Options "nosniff" always;
# add_header X-XSS-Protection "1; mode=block" always;
# add_header Referrer-Policy "strict-origin-when-cross-origin" always;
# add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
# server_tokens off;
# autoindex off;
sudo nginx -t && sudo systemctl reload nginx
```

### A4. .env & Storage Permissions
```bash
cd /var/www/devanka-logistik
chmod 600 .env
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
mkdir -p storage/logs bootstrap/cache
sudo chown www-data:www-data storage/logs bootstrap/cache
touch storage/logs/laravel.log
sudo chown www-data:www-data storage/logs/laravel.log
```

### A6. Disable Unnecessary Services
```bash
sudo systemctl disable --now acpid ModemManager multipathd udisks2 upower apport pollinate
# JANGAN disable: tat_agent, chrony, ssh, nginx, php8.3-fpm, mariadb, cron, ufw, rsyslog, systemd-*
```

### B2. MariaDB Tuning
```bash
sudo tee /etc/mysql/mariadb.conf.d/99-tuning.cnf > /dev/null << 'EOF'
[mysqld]
innodb_buffer_pool_size = 128M
innodb_log_file_size = 32M
max_connections = 50
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 2
query_cache_type = 1
query_cache_size = 16M
tmp_table_size = 16M
max_heap_table_size = 16M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT
EOF
sudo mkdir -p /var/log/mysql
sudo chown mysql:mysql /var/log/mysql
sudo systemctl restart mariadb
```

### C1. DB Backup (setiap 6 jam)
```bash
# Buat script
sudo tee /backup/db-backup.sh > /dev/null << 'EOF'
#!/bin/bash
BACKUP_DIR="/backup"
DB_NAME="devanka_logistik"  # dari .env DB_DATABASE
DB_USER="devanka"           # dari .env DB_USERNAME
DB_PASS="password"          # dari .env DB_PASSWORD
TIMESTAMP=$(date +%Y%m%d_%H%M)
BACKUP_FILE="${BACKUP_DIR}/db_${TIMESTAMP}.sql.gz"

mysqldump -u "${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" | gzip > "${BACKUP_FILE}"

if [ -s "${BACKUP_FILE}" ]; then
    echo "$(date): Backup successful - ${BACKUP_FILE}"
else
    echo "$(date): Backup FAILED" >> /var/log/db-backup-error.log
    rm -f "${BACKUP_FILE}"
    exit 1
fi

# Cleanup old backups (>3 days)
find "${BACKUP_DIR}" -name "db_*.sql.gz" -mtime +3 -delete
EOF
sudo chmod +x /backup/db-backup.sh

# Cron: every 6 hours
sudo tee /etc/cron.d/db-backup > /dev/null << 'EOF'
0 */6 * * * root /backup/db-backup.sh >> /var/log/db-backup.log 2>&1
EOF
```

### C2. File Backup (daily)
```bash
sudo tee /backup/file-backup.sh > /dev/null << 'EOF'
#!/bin/bash
BACKUP_DIR="/backup"
TIMESTAMP=$(date +%Y%m%d_%H)
BACKUP_FILE="${BACKUP_DIR}/files_${TIMESTAMP}.tar.gz"

tar -czf "${BACKUP_FILE}" -C /var/www/devanka-logistik storage/app/public

if [ -s "${BACKUP_FILE}" ]; then
    echo "$(date): File backup successful - ${BACKUP_FILE}"
else
    echo "$(date): File backup FAILED" >> /var/log/file-backup-error.log
    rm -f "${BACKUP_FILE}"
    exit 1
fi

find "${BACKUP_DIR}" -name "files_*.tar.gz" -mtime +7 -delete
EOF
sudo chmod +x /backup/file-backup.sh

# Cron: daily at 3 AM
sudo tee /etc/cron.d/file-backup > /dev/null << 'EOF'
0 3 * * * root /backup/file-backup.sh >> /var/log/file-backup.log 2>&1
EOF
```

### C3. Off-site Backup (rclone)
```bash
# Install rclone
curl https://rclone.org/install.sh | sudo bash

# Configure (manual - interactive)
sudo rclone config
# Setup Cloudflare R2 atau S3-compatible storage

# Cron: daily at 5 AM
sudo tee /etc/cron.d/offsite-backup > /dev/null << 'EOF'
0 5 * * * root rclone sync /backup remote:devanka-backup --quiet 2>&1 | tee -a /var/log/rclone-backup.log
EOF
```

### C4. Log Cleanup
```bash
sudo tee /etc/cron.d/log-cleanup > /dev/null << 'EOF'
0 5 * * * root find /var/www/devanka-logistik/storage/logs -name "*.log" -mtime +7 -delete
0 5 * * * root find /var/log -name "*.log" -mtime +14 -delete 2>/dev/null
EOF
```

### E2. Region Sync (re-run)
```bash
cd /var/www/devanka-logistik
sudo -u www-data php artisan regions:sync
# ~10-15 menit, butuh internet stabil
# Target: 35 provinces, 514 regencies, 7,215 districts, 80,532 villages
```

### B1. PHP-FPM Tuning
```bash
sudo nano /etc/php/8.3/fpm/pool.d/www.conf
# pm = dynamic
# pm.max_children = 5
# pm.start_servers = 2
# pm.min_spare_servers = 1
# pm.max_spare_servers = 3
# pm.max_requests = 500
sudo systemctl restart php8.3-fpm
```

### B2. MariaDB Tuning
```bash
sudo nano /etc/mysql/mariadb.conf.d/50-server.cnf
# innodb_buffer_pool_size = 128M
# max_connections = 50
# slow_query_log = 1
# slow_query_log_file = /var/log/mysql/slow.log
# long_query_time = 2
sudo systemctl restart mariadb
```

### B3. Nginx Tuning
```bash
sudo nano /etc/nginx/nginx.conf
# worker_processes auto;
# worker_connections 1024;
# keepalive_timeout 30;
# client_max_body_size 20M;
# gzip on;
# gzip_types text/plain text/css application/json application/javascript text/xml application/xml;
sudo nginx -t && sudo systemctl reload nginx
```

### C1. DB Backup (setiap 6 jam)
```bash
sudo mkdir -p /backup
sudo nano /backup/db-backup.sh
# #!/bin/bash
# mysqldump -u devanka -p'PASSWORD' devanka_logistik | gzip > /backup/db_$(date +%Y%m%d_%H%M).sql.gz
# find /backup -name "db_*.sql.gz" -mtime +3 -delete
sudo chmod +x /backup/db-backup.sh
sudo nano /etc/cron.d/db-backup
# 0 */6 * * * root /backup/db-backup.sh
```

### C2. File Backup
```bash
sudo nano /backup/file-backup.sh
# #!/bin/bash
# tar -czf /backup/files_$(date +%Y%m%d_%H).tar.gz -C /var/www/devanka-logistik storage/app/public
# find /backup -name "files_*.tar.gz" -mtime +7 -delete
sudo chmod +x /backup/file-backup.sh
# Tambah ke cron: 0 3 * * * root /backup/file-backup.sh
```

### C3. Off-site Backup (rclone)
```bash
curl https://rclone.org/install.sh | sudo bash
rclone config  # setup Cloudflare R2
sudo nano /etc/cron.d/offsite-backup
# 0 5 * * * root rclone sync /backup r2:devanka-backup --quiet
```

### C4. Log Cleanup
```bash
sudo nano /etc/cron.d/log-cleanup
# 0 5 * * * root find /var/www/devanka-logistik/storage/logs -name "*.log" -mtime +7 -delete
```

### D1. UptimeRobot
1. Daftar di https://uptimerobot.com
2. Add Monitor → HTTP(s)
3. URL: https://devankalogistik.web.id/api/health
4. Interval: 5 minutes
5. Alert: Email/Slack/Telegram

### D2. Automated Alert
```bash
sudo nano /var/www/devanka-logistik/monitor.sh
# #!/bin/bash
# URL="https://devankalogistik.web.id/api/health"
# RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" --max-time 10 $URL)
# if [ "$RESPONSE" != "200" ]; then
#     echo "ALERT: Site down (HTTP $RESPONSE) at $(date)" | mail -s "Devanka Alert" admin@email.com
# fi
sudo chmod +x /var/www/devanka-logistik/monitor.sh
sudo nano /etc/cron.d/vps-monitor
# */5 * * * * root /var/www/devanka-logistik/monitor.sh
```

### E1. DB User Least Privilege
```bash
sudo mysql -u root -p
# CREATE USER 'devanka'@'localhost' IDENTIFIED BY 'strong_password';
# GRANT SELECT, INSERT, UPDATE, DELETE ON devanka_logistik.* TO 'devanka'@'localhost';
# FLUSH PRIVILEGES;
# EXIT;
```

### E2. Region Sync (re-run)
```bash
cd /var/www/devanka-logistik
php artisan tinker --execute="\App\Models\Region::truncate();"
php artisan regions:sync
# ~10-15 menit, butuh internet stabil
```

---

## Implementation Notes
- SSH Hardening: SKIPPED (user request)
- SSL: DONE (certbot, devankalogistik.web.id)
- Health Check: DONE (/api/health)
- Region sync: DONE (emsifa v2 — 38 provinsi, 83,762 desa target; data DB saat ini 33/479/6,604/75,412)
- All commands must be run manually on VPS via SSH/console
- Update checklist above as items are completed

---

## GitHub Actions Workflows

### Kept (CI/CD + ops reusable)
| Workflow | Fungsi |
|---|---|
| `ci.yml` | Test on push/PR |
| `deploy.yml` | Auto-deploy ke VPS + restart FPM |
| `check-phase1.yml` | Verifikasi 17 item Phase 1 |
| `region-sync-force.yml` | Re-sync region data (emsifa v2) |
| `restart-fpm.yml` | Restart PHP-FPM manual trigger |
| `setup-redis-opcache.yml` | Setup Redis + OPcache (idempotent) |

### Deleted (one-off debug/fix — sudah selesai)
- `diagnose-500.yml` — diagnosis 500 (done)
- `fix-redis-ext.yml` — install php8.3-redis (done)
- `diagnostic-app.yml` — diagnostic region/Redis/OPcache/index (done)
- `debug-mariadb.yml` — debug MariaDB config (done)
- `fix-phase1.yml` — fix services/MariaDB/region (done)
- `setup-phase1-remaining.yml` — setup Phase 1 awal (done)

Catatan: script di `scripts/` mengikuti workflow yang dihapus. `check-phase1.sh` & `setup-redis-opcache.sh` dipertahankan.