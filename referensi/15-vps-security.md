# VPS Security Hardening — Phase 1

> Implementasi LANGSUNG di VPS via SSH. Tidak lewat GitHub.
> Semua command dijalankan sebagai user dengan sudo access.

## 1. UFW Firewall

**Tujuan**: Blokir semua port yang tidak perlu. Hanya buka 22 (SSH), 80 (HTTP), 443 (HTTPS).

```bash
# Install UFW (biasanya sudah ada di Ubuntu)
sudo apt install ufw -y

# Default: deny incoming, allow outgoing
sudo ufw default deny incoming
sudo ufw default allow outgoing

# Allow SSH, HTTP, HTTPS
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Enable firewall
sudo ufw enable

# Cek status
sudo ufw status verbose
```

**Catatan**: Jika SSH di port custom, sesuaikan `22/tcp` dengan port yang dipakai.

**Verifikasi**: `sudo ufw status` → harus menampilkan 3 rules (22, 80, 443).

---

## 2. SSH Hardening

**Tujuan**: Nonaktifkan login root & password. Hanya SSH key. Cegah brute force.

### Generate SSH Key (di local PC)
```bash
ssh-keygen -t ed25519 -C "ambon@devanka"
# Copy public key ke VPS
ssh-copy-id ubuntu@43.173.50.217
```

### Edit SSH Config
```bash
sudo nano /etc/ssh/sshd_config
```

Set nilai berikut (uncomment/edit):
```
PermitRootLogin no
PasswordAuthentication no
PubkeyAuthentication yes
MaxAuthTries 3
LoginGraceTime 30
X11Forwarding no
```

### Restart SSH
```bash
sudo systemctl restart sshd
```

**Verifikasi**: Coba login dengan password → harus ditolak. Login dengan key → berhasil.

**PENTING**: Pastikan SSH key sudah berfungsi sebelum disable password, atau lo akan terkunci.

---

## 3. Fail2ban

**Tujuan**: Auto-block IP yang gagal login SSH berkali-kali.

```bash
sudo apt install fail2ban -y
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

### Config
```bash
sudo nano /etc/fail2ban/jail.local
```
Isi:
```
[DEFAULT]
bantime = 3600
findtime = 600
maxretry = 3

[sshd]
enabled = true
port = ssh
filter = sshd
logpath = /var/log/auth.log
maxretry = 3
bantime = 3600
```

### Restart
```bash
sudo systemctl restart fail2ban
```

### Cek Status
```bash
sudo fail2ban-client status sshd
```

---

## 4. SSL (Let's Encrypt)

**Tujuan**: Enkripsi HTTPS. Gratis, auto-renewal.

```bash
sudo apt install certbot python3-certbot-nginx -y
sudo certbot --nginx -d devankalogistik.web.id
```

Certbot akan otomatis:
- Generate sertifikat
- Update Nginx config (tambah listen 443 ssl)
- Setup auto-renewal (systemd timer)

### Verifikasi Auto-Renewal
```bash
sudo certbot renew --dry-run
```

### Catatan
- Domain harus sudah pointing ke IP VPS (A record)
- Port 80 harus terbuka (untuk challenge)
- Sertifikat berlaku 90 hari, auto-renew 30 hari sebelum expired

---

## 5. Nginx Security Headers

**Tujuan**: Tambah header keamanan untuk cegah clickjacking, MIME sniffing, XSS.

### Edit Nginx Site Config
```bash
sudo nano /etc/nginx/sites-available/devanka-logistik
```

Tambahkan di dalam `server` block:
```nginx
# Security headers
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-Content-Type-Options "nosniff" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

# Disable directory listing
autoindex off;

# Hide Nginx version
server_tokens off;
```

### Reload
```bash
sudo nginx -t && sudo systemctl reload nginx
```

### Verifikasi
```bash
curl -I https://devankalogistik.web.id
# Harus menampilkan security headers
```

---

## 6. Laravel-specific Security (VPS)

### .env Permissions
```bash
cd /var/www/devanka-logistik
chmod 600 .env
```

### Storage Permissions
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Disable Directory Listing (sudah di Nginx config di atas)

### APP_DEBUG
Pastikan di `.env`:
```
APP_DEBUG=false
APP_ENV=production
```

---

## 7. Disable Unnecessary Services

**Tujuan**: Hemat RAM/CPU dengan menonaktifkan service yang tidak perlu di server.

```bash
# Cek service aktif
systemctl list-units --type=service --state=running

# Disable yang tidak perlu (jika ada)
sudo systemctl disable --now bluetooth 2>/dev/null
sudo systemctl disable --now cups 2>/dev/null
sudo systemctl disable --now avahi-daemon 2>/dev/null
sudo systemctl disable --now ModemManager 2>/dev/null
sudo systemctl disable --now snapd 2>/dev/null
sudo systemctl disable --now postfix 2>/dev/null
```

**Catatan**: Hanya disable jika service tersebut tidak dipakai. Cek dulu dengan `systemctl status <service>`.

---

## 8. PHP-FPM Tuning

**Tujuan**: Batasi worker process sesuai RAM. Mencegah OOM.

### Edit Pool Config
```bash
sudo nano /etc/php/8.3/fpm/pool.d/www.conf
```

Set:
```
pm = dynamic
pm.max_children = 10        # 1 child ~50MB, 10 child ~500MB
pm.start_servers = 3
pm.min_spare_servers = 2
pm.max_spare_servers = 5
pm.max_requests = 500       # recycle worker (hindari memory leak)
```

### Restart
```bash
sudo systemctl restart php8.3-fpm
```

### Verifikasi
```bash
sudo systemctl status php8.3-fpm
```

---

## 9. MariaDB Tuning

**Tujuan**: Optimasi buffer pool, enable slow query log.

### Edit Config
```bash
sudo nano /etc/mysql/mariadb.conf.d/50-server.cnf
```

Tambahkan/edit di section `[mysqld]`:
```
innodb_buffer_pool_size = 256M
max_connections = 50
query_cache_size = 0
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 2
```

### Restart
```bash
sudo systemctl restart mariadb
```

### Verifikasi
```bash
sudo mysql -e "SHOW VARIABLES LIKE 'innodb_buffer_pool_size';"
sudo mysql -e "SHOW VARIABLES LIKE 'slow_query_log';"
```

---

## 10. Nginx Tuning

**Tujuan**: Gzip, keepalive, worker connections untuk throughput.

### Edit Nginx Config
```bash
sudo nano /etc/nginx/nginx.conf
```

Set di section `events` dan `http`:
```nginx
events {
    worker_connections 1024;
    use epoll;
    multi_accept on;
}

http {
    worker_processes auto;
    keepalive_timeout 30;
    client_max_body_size 20M;
    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml;
    gzip_min_length 1000;
    # ... rest of config
}
```

### Reload
```bash
sudo nginx -t && sudo systemctl reload nginx
```

---

## Checklist Implementasi Phase 1

- [ ] UFW firewall enabled (22, 80, 443)
- [ ] SSH key-only login (password disabled)
- [ ] Fail2ban installed & running
- [ ] SSL certificate installed (certbot)
- [ ] Nginx security headers added
- [ ] .env chmod 600
- [ ] Storage permissions (www-data)
- [ ] APP_DEBUG=false
- [ ] Unnecessary services disabled
- [ ] PHP-FPM tuned
- [ ] MariaDB tuned
- [ ] Nginx tuned

## Catatan
- Semua config ini ada di VPS, BUKAN di repo GitHub
- Simpan copy config di folder `referensi/` repo sebagai dokumentasi
- Jika VPS crash, gunakan checklist ini untuk setup ulang