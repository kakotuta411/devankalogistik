#!/bin/bash
# Phase 1 Remaining Setup Script
# Items: Disable services, MariaDB tuning, DB backup, File backup, Rclone, Region sync
# Run via GitHub Actions or manually on VPS

set -e

echo "========================================"
echo "  PHASE 1 REMAINING SETUP"
echo "  $(date)"
echo "========================================"
echo ""

# ============================================
# 1. Disable Unnecessary Services
# ============================================
echo "1. Disabling unnecessary services..."
sudo systemctl disable --now acpid ModemManager multipathd udisks2 upower apport pollinate 2>/dev/null || true
echo "   ✅ Done"
echo ""

# ============================================
# 2. MariaDB Tuning
# ============================================
echo "2. MariaDB Tuning..."
sudo tee /etc/mysql/mariadb.conf.d/99-tuning.cnf > /dev/null << 'EOF'
[mysqld]
# Memory tuning (VPS 2GB RAM)
innodb_buffer_pool_size = 128M
innodb_log_file_size = 32M
max_connections = 50

# Slow query log
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 2

# Performance
query_cache_type = 1
query_cache_size = 16M
tmp_table_size = 16M
max_heap_table_size = 16M

# InnoDB
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT
EOF

sudo mkdir -p /var/log/mysql
sudo chown mysql:mysql /var/log/mysql
sudo systemctl restart mariadb
echo "   ✅ MariaDB tuned (buffer_pool=128M, slow_query_log=2s)"
echo ""

# ============================================
# 3. DB Backup Script
# ============================================
echo "3. DB Backup Setup..."
sudo mkdir -p /backup

# Get DB credentials from .env
DB_NAME=$(grep "^DB_DATABASE=" /var/www/devanka-logistik/.env | cut -d= -f2)
DB_USER=$(grep "^DB_USERNAME=" /var/www/devanka-logistik/.env | cut -d= -f2)
DB_PASS=$(grep "^DB_PASSWORD=" /var/www/devanka-logistik/.env | cut -d= -f2)

sudo tee /backup/db-backup.sh > /dev/null << EOF
#!/bin/bash
# Database backup script - runs every 6 hours
# Retention: 3 days

BACKUP_DIR="/backup"
DB_NAME="$DB_NAME"
DB_USER="$DB_USER"
DB_PASS="$DB_PASS"
TIMESTAMP=\$(date +%Y%m%d_%H%M)
BACKUP_FILE="\${BACKUP_DIR}/db_\${TIMESTAMP}.sql.gz"

# Create backup
mysqldump -u "\${DB_USER}" -p"\${DB_PASS}" "\${DB_NAME}" | gzip > "\${BACKUP_FILE}"

# Verify backup
if [ -s "\${BACKUP_FILE}" ]; then
    echo "\$(date): Backup successful - \${BACKUP_FILE} (\$(du -h \${BACKUP_FILE} | cut -f1))"
else
    echo "\$(date): Backup FAILED - \${BACKUP_FILE}" >> /var/log/db-backup-error.log
    rm -f "\${BACKUP_FILE}"
    exit 1
fi

# Cleanup old backups (>3 days)
find "\${BACKUP_DIR}" -name "db_*.sql.gz" -mtime +3 -delete
EOF

sudo chmod +x /backup/db-backup.sh

# Cron: every 6 hours
sudo tee /etc/cron.d/db-backup > /dev/null << 'EOF'
# Database backup - every 6 hours
0 */6 * * * root /backup/db-backup.sh >> /var/log/db-backup.log 2>&1
EOF

echo "   ✅ DB backup script + cron (every 6h, retention 3 days)"
echo ""

# ============================================
# 4. File Backup Script
# ============================================
echo "4. File Backup Setup..."
sudo tee /backup/file-backup.sh > /dev/null << 'EOF'
#!/bin/bash
# File backup script - storage/app/public
# Retention: 7 days

BACKUP_DIR="/backup"
SOURCE_DIR="/var/www/devanka-logistik/storage/app/public"
TIMESTAMP=$(date +%Y%m%d_%H)
BACKUP_FILE="${BACKUP_DIR}/files_${TIMESTAMP}.tar.gz"

# Create backup
tar -czf "${BACKUP_FILE}" -C /var/www/devanka-logistik storage/app/public

# Verify backup
if [ -s "${BACKUP_FILE}" ]; then
    echo "$(date): File backup successful - ${BACKUP_FILE} ($(du -h ${BACKUP_FILE} | cut -f1))"
else
    echo "$(date): File backup FAILED - ${BACKUP_FILE}" >> /var/log/file-backup-error.log
    rm -f "${BACKUP_FILE}"
    exit 1
fi

# Cleanup old backups (>7 days)
find "${BACKUP_DIR}" -name "files_*.tar.gz" -mtime +7 -delete
EOF

sudo chmod +x /backup/file-backup.sh

# Cron: daily at 3 AM
sudo tee /etc/cron.d/file-backup > /dev/null << 'EOF'
# File backup - daily at 3 AM
0 3 * * * root /backup/file-backup.sh >> /var/log/file-backup.log 2>&1
EOF

echo "   ✅ File backup script + cron (daily 3 AM, retention 7 days)"
echo ""

# ============================================
# 5. Rclone (Off-site Backup)
# ============================================
echo "5. Rclone Setup..."
if ! command -v rclone &>/dev/null; then
    curl https://rclone.org/install.sh | sudo bash
    echo "   ✅ Rclone installed"
else
    echo "   ✅ Rclone already installed"
fi

# Create rclone config directory
sudo mkdir -p /root/.config/rclone

# Cron: daily at 5 AM (sync /backup to remote)
sudo tee /etc/cron.d/offsite-backup > /dev/null << 'EOF'
# Off-site backup - daily at 5 AM
0 5 * * * root rclone sync /backup remote:devanka-backup --quiet 2>&1 | tee -a /var/log/rclone-backup.log
EOF

echo "   ⚠️  Rclone config needed: run 'rclone config' manually to setup Cloudflare R2"
echo "   📝 Cron configured (daily 5 AM) - will work after rclone config"
echo ""

# ============================================
# 6. Log Cleanup Cron
# ============================================
echo "6. Log Cleanup Setup..."
sudo tee /etc/cron.d/log-cleanup > /dev/null << 'EOF'
# Log cleanup - daily at 5 AM
0 5 * * * root find /var/www/devanka-logistik/storage/logs -name "*.log" -mtime +7 -delete
0 5 * * * root find /var/log -name "*.log" -mtime +14 -delete 2>/dev/null
EOF
echo "   ✅ Log cleanup configured (7 days for app logs, 14 days for system logs)"
echo ""

# ============================================
# 7. Region Sync (re-run)
# ============================================
echo "7. Region Sync (re-run)..."
cd /var/www/devanka-logistik
sudo -u www-data php artisan regions:sync 2>&1 | tail -5
echo "   ✅ Region sync triggered"
echo ""

# ============================================
# Summary
# ============================================
echo "========================================"
echo "  SETUP COMPLETE"
echo "========================================"
echo ""
echo "Manual steps remaining:"
echo "  1. Configure rclone: sudo rclone config (setup Cloudflare R2)"
echo "  2. Verify region sync: php artisan tinker (check village count)"
echo "  3. Test backup: sudo /backup/db-backup.sh"
echo "  4. Test file backup: sudo /backup/file-backup.sh"
echo ""
echo "Cron jobs configured:"
echo "  - DB backup: every 6 hours (retention 3 days)"
echo "  - File backup: daily 3 AM (retention 7 days)"
echo "  - Off-site: daily 5 AM (rclone sync)"
echo "  - Log cleanup: daily 5 AM"
echo ""