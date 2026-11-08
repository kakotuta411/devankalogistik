# Phase 1 Implementation Tracker — VPS Security & Operations

> Status: IN PROGRESS
> SSH Hardening: SKIPPED (user request)
> Last Updated: 2026-11-08

---

## Implementation Checklist

### A. Security
- [ ] UFW Firewall (port 22, 80, 443)
- [x] SSH Hardening — SKIPPED (user request)
- [ ] Fail2ban (brute force protection)
- [ ] SSL certificate (certbot) — DONE (devankalogistik.web.id)
- [ ] Nginx security headers (X-Frame-Options, HSTS, etc.)
- [ ] .env chmod 600
- [ ] Storage permissions (www-data)
- [ ] APP_DEBUG=false (sudah)
- [ ] Disable unnecessary services

### B. Tuning
- [ ] PHP-FPM pool tuning (max_children=5)
- [ ] MariaDB tuning (buffer_pool=128M, slow query log)
- [ ] Nginx tuning (gzip, keepalive, worker connections)

### C. Backup
- [ ] DB backup cron (setiap 6 jam, retention 3 hari)
- [ ] File backup (storage/app/public, retention 7 hari)
- [ ] Off-site backup (rclone → Cloudflare R2)
- [ ] Backup verification (weekly)
- [ ] Log cleanup cron (7 hari)

### D. Monitoring
- [ ] UptimeRobot setup (ping /api/health, 5 min interval)
- [ ] Automated alert cron (monitor.sh)
- [ ] Health check endpoint — DONE (/api/health)

### E. Manual (VPS)
- [ ] DB user least privilege (GRANT SELECT/INSERT/UPDATE/DELETE)
- [ ] Region sync (re-run, villages incomplete)

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

### A5. Disable Unnecessary Services
```bash
sudo systemctl disable --now acpid ModemManager multipathd udisks2 upower getty@tty1 serial-getty@ttyS0 apport thermald gpu-manager pollinate sysstat inetutils-inetd
# JANGAN disable: tat_agent, chrony, ssh, nginx, php8.3-fpm, mariadb, cron, ufw, rsyslog, systemd-*
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
- Region sync: INCOMPLETE (10,585/80,532 villages) — re-run needed
- All commands must be run manually on VPS via SSH/console
- Update checklist above as items are completed