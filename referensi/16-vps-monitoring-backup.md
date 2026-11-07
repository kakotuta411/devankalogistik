# VPS Monitoring & Backup — Phase 1

> Implementasi LANGSUNG di VPS via SSH. Tidak lewat GitHub.
> Semua command dijalankan sebagai user dengan sudo access.

---

## 1. Health Check Endpoint (Laravel)

**Tujuan**: URL yang bisa di-ping oleh UptimeRobot untuk cek apakah site up.

### Tambahkan di routes/api.php
```php
Route::get('/health', function () {
    try {
        $db = DB::connection()->getPdo() ? 'connected' : 'disconnected';
    } catch (\Exception $e) {
        $db = 'disconnected';
    }
    return response()->json([
        'status' => 'ok',
        'db' => $db,
        'time' => now()->toIso8601String(),
    ]);
});
```

**Catatan**: Ini adalah code change → masuk Phase 2 (via GitHub push). Tapi bisa juga langsung edit di VPS.

### Akses
```
https://devakalogistik.web.id/api/health
```
Response:
```json
{"status":"ok","db":"connected","time":"2026-11-08T10:00:00+07:00"}
```

---

## 2. Uptime Monitoring (External)

**Tujuan**: Service external yang ping URL setiap 5 menit. Jika down > 5 min, kirim alert.

### UptimeRobot (Gratis)
1. Daftar di https://uptimerobot.com
2. Add Monitor → HTTP(s)
3. URL: `https://devakalogistik.web.id/api/health`
4. Interval: 5 minutes
5. Alert: Email / Slack / Telegram

### BetterStack (Gratis tier)
1. Daftar di https://betterstack.com
2. Create Monitor → HTTP
3. URL: `https://devakalogistik.web.id/api/health`
4. Alert: Email / Slack / SMS

**Rekomendasi**: Pakai UptimeRobot (gratis, simple) untuk mulai.

---

## 3. Server Resource Monitoring

### Install Tools
```bash
sudo apt install htop iftop iotop nethogs -y
```

### CPU & RAM
```bash
# Real-time (interactive)
htop

# Snapshot (non-interactive)
top -bn1 | head -5

# RAM
free -h

# Per-process (sorted by memory)
ps aux --sort=-%mem | head -10

# Per-process (sorted by CPU)
ps aux --sort=-%cpu | head -10
```

### Disk
```bash
# Disk usage
df -h

# Folder size (project)
du -sh /var/www/devanka-logistik/*

# Storage folder size
du -sh /var/www/devanka-logistik/storage/*

# Find large files
find /var/www/devanka-logistik -type f -size +10M -exec ls -lh {} \;
```

### Network
```bash
# Active connections
ss -tuln

# Bandwidth real-time
sudo iftop

# Per-process bandwidth
sudo nethogs
```

### I/O
```bash
# Disk I/O real-time
sudo iotop
```

---

## 4. Log Monitoring

### Laravel Errors
```bash
# Real-time tail
tail -f /var/www/devanka-logistik/storage/logs/laravel.log

# Last 20 errors
grep -i "ERROR\|Exception\|SQLSTATE" /var/www/devanka-logistik/storage/logs/laravel.log | tail -20

# Log file size
du -sh /var/www/devanka-logistik/storage/logs/
```

### Nginx Errors
```bash
sudo tail -f /var/log/nginx/error.log
sudo tail -f /var/log/nginx/access.log
```

### System Auth (SSH attempts)
```bash
sudo tail -f /var/log/auth.log

# Failed SSH attempts
sudo grep "Failed password" /var/log/auth.log | tail -10

# Fail2ban bans
sudo fail2ban-client status sshd
```

### MariaDB
```bash
sudo tail -f /var/log/mysql/error.log
sudo tail -f /var/log/mysql/slow.log
```

---

## 5. Automated Alert (Cron)

**Tujuan**: Jika site down, kirim email alert.

### Install mailutils
```bash
sudo apt install mailutils -y
```

### Create Monitor Script
```bash
sudo nano /var/www/devanka-logistik/monitor.sh
```
Isi:
```bash
#!/bin/bash
URL="https://devakalogistik.web.id/api/health"
RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" --max-time 10 $URL)
if [ "$RESPONSE" != "200" ]; then
    echo "ALERT: Site down (HTTP $RESPONSE) at $(date)" | mail -s "Devanka Alert" admin@email.com
fi
```

### Make Executable
```bash
chmod +x /var/www/devanka-logistik/monitor.sh
```

### Add Cron
```bash
sudo nano /etc/cron.d/vps-monitor
```
Isi:
```
*/5 * * * * root /var/www/devanka-logistik/monitor.sh
```

---

## 6. Database Backup (Daily Cron)

**Tujuan**: Backup DB setiap 6 jam. Simpan file .sql.gz. Retention 3 hari.

### Create Backup Directory
```bash
sudo mkdir -p /backup
sudo chmod 755 /backup
```

### Create Backup Script
```bash
sudo nano /backup/db-backup.sh
```
Isi:
```bash
#!/bin/bash
DB_NAME="devanka_logistik"
DB_USER="devanka"
DB_PASS="your_password"
BACKUP_DIR="/backup"
DATE=$(date +%Y%m%d_%H%M)

mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_${DATE}.sql.gz

# Retention: hapus backup > 3 hari
find $BACKUP_DIR -name "db_*.sql.gz" -mtime +3 -delete
```

### Make Executable
```bash
chmod +x /backup/db-backup.sh
```

### Add Cron (setiap 6 jam)
```bash
sudo nano /etc/cron.d/db-backup
```
Isi:
```
0 */6 * * * root /backup/db-backup.sh
```

### Verifikasi
```bash
# Jalankan manual
/backup/db-backup.sh
ls -lh /backup/
```

---

## 7. File Backup (Storage)

**Tujuan**: Backup storage/app/public (logo, POD photos, print settings).

### Add to Backup Script
Tambahkan ke `/backup/db-backup.sh` atau buat script terpisah:
```bash
#!/bin/bash
# ... DB backup di atas ...

# File backup
tar -czf /backup/files_$(date +%Y%m%d_%H).tar.gz -C /var/www/devanka-logistik storage/app/public

# Retention files > 7 hari
find /backup -name "files_*.tar.gz" -mtime +7 -delete
```

---

## 8. Off-site Backup (Cloud)

**Tujuan**: Sync backup ke cloud. Jika VPS crash total, data tetap ada.

### Install rclone
```bash
curl https://rclone.org/install.sh | sudo bash
```

### Configure (Cloudflare R2 atau S3)
```bash
rclone config
# Pilih: n (new remote)
# Name: r2
# Type: s3
# Provider: Cloudflare
# Access Key ID: (dari R2 dashboard)
# Secret Access Key: (dari R2 dashboard)
# Endpoint: https://<account_id>.r2.cloudflarestorage.com
```

### Sync Cron
```bash
sudo nano /etc/cron.d/offsite-backup
```
Isi:
```
0 5 * * * root rclone sync /backup r2:devanka-backup --quiet
```

### Verifikasi
```bash
rclone ls r2:devanka-backup
```

---

## 9. Backup Verification

**Tujuan**: Pastikan backup bisa di-restore (tidak corrupt).

### Test Restore
```bash
# Buat DB test
sudo mysql -e "CREATE DATABASE IF NOT EXISTS devanka_logistik_test;"

# Restore dari backup terbaru
gunzip < /backup/db_$(ls /backup/db_*.sql.gz | tail -1 | xargs basename) | mysql -u devanka -p devanka_logistik_test

# Verifikasi
sudo mysql -e "USE devanka_logistik_test; SELECT COUNT(*) FROM shipments; SELECT COUNT(*) FROM invoices;"

# Hapus DB test
sudo mysql -e "DROP DATABASE devanka_logistik_test;"
```

### Automate Verification (Weekly)
```bash
sudo nano /etc/cron.d/backup-verify
```
Isi:
```
0 6 * * 0 root /backup/verify-backup.sh
```

Script `/backup/verify-backup.sh`:
```bash
#!/bin/bash
LATEST=$(ls /backup/db_*.sql.gz | sort | tail -1)
sudo mysql -e "DROP DATABASE IF EXISTS devanka_logistik_test; CREATE DATABASE devanka_logistik_test;"
gunzip < $LATEST | mysql -u devanka -p'password' devanka_logistik_test
COUNT=$(mysql -u devanka -p'password' -N -e "SELECT COUNT(*) FROM devanka_logistik_test.shipments;")
echo "Backup verification: $COUNT shipments found" >> /var/log/backup-verify.log
sudo mysql -e "DROP DATABASE devanka_logistik_test;"
```

---

## 10. Resource Analysis Commands

### Quick Health Check
```bash
# One-liner status
echo "=== CPU ===" && top -bn1 | grep "Cpu(s)" && echo "=== RAM ===" && free -h | grep Mem && echo "=== DISK ===" && df -h / && echo "=== SERVICES ===" && systemctl list-units --type=service --state=running | wc -l
```

### Laravel-specific
```bash
# Cache size
du -sh /var/www/devanka-logistik/storage/framework/cache/data

# Log size
du -sh /var/www/devanka-logistik/storage/logs

# Session count
ls /var/www/devanka-logistik/storage/framework/sessions | wc -l

# View compiled size
du -sh /var/www/devanka-logistik/storage/framework/views
```

### Cron Cleanup (Laravel logs)
```bash
# Add to cron (daily 5 AM)
0 5 * * * root find /var/www/devanka-logistik/storage/logs -name "*.log" -mtime +7 -delete
```

---

## Checklist Implementasi Phase 1 (Monitoring & Backup)

- [ ] Health check endpoint (Phase 2, tapi bisa langsung)
- [ ] UptimeRobot configured
- [ ] Monitoring tools installed (htop, iftop, iotop)
- [ ] Log monitoring commands documented
- [ ] Automated alert cron (monitor.sh)
- [ ] Backup directory created (/backup)
- [ ] DB backup cron (setiap 6 jam)
- [ ] File backup cron (storage/app/public)
- [ ] Off-site backup (rclone → R2/S3)
- [ ] Backup verification (weekly)
- [ ] Log cleanup cron

## Catatan
- Semua config ini ada di VPS, BUKAN di repo GitHub
- Cron jobs tersimpan di `/etc/cron.d/`
- Backup scripts tersimpan di `/backup/`
- Simpan copy config di folder `referensi/` repo sebagai dokumentasi