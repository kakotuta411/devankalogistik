#!/bin/bash
# Phase 1 VPS Checklist Verification Script
# Run via GitHub Actions or manually on VPS

echo "========================================"
echo "  PHASE 1 VPS CHECKLIST VERIFICATION"
echo "  $(date)"
echo "========================================"
echo ""

PASS=0
FAIL=0
WARN=0

check_item() {
    local name="$1"
    local status="$2"
    echo "$name: $status"
    if echo "$status" | grep -q "✅"; then ((PASS++)); fi
    if echo "$status" | grep -q "❌"; then ((FAIL++)); fi
    if echo "$status" | grep -q "⚠️"; then ((WARN++)); fi
}

# 1. UFW Firewall
echo "1. UFW Firewall:"
UFW_STATUS=$(sudo ufw status 2>/dev/null | head -1)
if echo "$UFW_STATUS" | grep -qi "active"; then
    check_item "   UFW" "✅ Active ($(sudo ufw status 2>/dev/null | grep -c 'ALLOW' || echo 0) rules)"
else
    check_item "   UFW" "❌ Not active"
fi
echo ""

# 2. Fail2ban
echo "2. Fail2ban:"
FB_STATUS=$(sudo fail2ban-client status sshd 2>/dev/null | grep -i "currently banned" | awk '{print $NF}')
if [ -n "$FB_STATUS" ]; then
    check_item "   Fail2ban" "✅ Running (banned: $FB_STATUS)"
else
    check_item "   Fail2ban" "❌ Not running"
fi
echo ""

# 3. Nginx Security Headers
echo "3. Nginx Security Headers:"
HEADERS=$(curl -sI https://devankalogistik.web.id 2>/dev/null)
if echo "$HEADERS" | grep -qi "x-frame-options"; then
    check_item "   Headers" "✅ Configured"
else
    check_item "   Headers" "❌ Not found"
fi
echo ""

# 4. Disable Services
echo "4. Unnecessary Services:"
SVC=$(systemctl list-unit-files --state=enabled 2>/dev/null | grep -cE "acpid|ModemManager|multipathd|udisks2|upower|apport|pollinate" || echo 0)
if [ "$SVC" -eq 0 ]; then
    check_item "   Services" "✅ None enabled (clean)"
else
    check_item "   Services" "⚠️ $SVC unnecessary services still enabled"
fi
echo ""

# 5. PHP-FPM Tuning
echo "5. PHP-FPM Tuning:"
PM_MAX=$(grep -E "^pm.max_children" /etc/php/8.3/fpm/pool.d/www.conf 2>/dev/null | awk '{print $3}')
if [ -n "$PM_MAX" ]; then
    check_item "   PHP-FPM" "✅ max_children=$PM_MAX"
else
    check_item "   PHP-FPM" "❌ Not configured"
fi
echo ""

# 6. MariaDB Tuning
echo "6. MariaDB Tuning:"
BP=$(grep -ri "innodb_buffer_pool_size" /etc/mysql/mariadb.conf.d/ 2>/dev/null | grep -v "^#" | head -1 | awk -F= '{print $2}' | tr -d ' ')
if [ -n "$BP" ]; then
    check_item "   MariaDB" "✅ buffer_pool=$BP"
else
    check_item "   MariaDB" "❌ Not configured"
fi
echo ""

# 7. Nginx Tuning
echo "7. Nginx Tuning:"
GZIP=$(grep -E "^\s*gzip on;" /etc/nginx/nginx.conf 2>/dev/null)
if [ -n "$GZIP" ]; then
    check_item "   Nginx" "✅ gzip enabled"
else
    check_item "   Nginx" "❌ gzip not enabled"
fi
echo ""

# 8. DB Backup Cron
echo "8. DB Backup:"
if [ -f /backup/db-backup.sh ]; then
    check_item "   DB Backup" "✅ Script exists"
else
    check_item "   DB Backup" "❌ Not setup"
fi
echo ""

# 9. File Backup
echo "9. File Backup:"
if [ -f /backup/file-backup.sh ]; then
    check_item "   File Backup" "✅ Script exists"
else
    check_item "   File Backup" "❌ Not setup"
fi
echo ""

# 10. Off-site Backup (rclone)
echo "10. Off-site Backup (rclone):"
if command -v rclone &>/dev/null; then
    check_item "   Rclone" "✅ Installed"
else
    check_item "   Rclone" "❌ Not installed"
fi
echo ""

# 11. SSL Certificate
echo "11. SSL Certificate:"
CERT=$(sudo certbot certificates 2>/dev/null | grep -i "VALID\|expiry" | head -1)
if [ -n "$CERT" ]; then
    check_item "   SSL" "✅ $CERT"
else
    check_item "   SSL" "❌ Not found"
fi
echo ""

# 12. Health Check Endpoint
echo "12. Health Check:"
HC=$(curl -s https://devankalogistik.web.id/api/health 2>/dev/null)
if echo "$HC" | grep -q '"status":"ok"'; then
    check_item "   Health" "✅ $HC"
else
    check_item "   Health" "❌ Not responding"
fi
echo ""

# 13. Storage Permissions
echo "13. Storage Permissions:"
OWNER=$(stat -c '%U:%G' /var/www/devanka-logistik/storage 2>/dev/null)
if [ "$OWNER" = "www-data:www-data" ]; then
    check_item "   Storage" "✅ Owned by www-data"
else
    check_item "   Storage" "⚠️ Owned by $OWNER (expected www-data:www-data)"
fi
echo ""

# 14. Region Sync
echo "14. Region Sync:"
cd /var/www/devanka-logistik
REGIONS=$(php artisan tinker --execute="
echo \App\Models\Region::where('level','province')->count().','. 
\App\Models\Region::where('level','regency')->count().','.
\App\Models\Region::where('level','district')->count().','.
\App\Models\Region::where('level','village')->count();
" 2>/dev/null | tail -1)
P=$(echo "$REGIONS" | cut -d, -f1)
R=$(echo "$REGIONS" | cut -d, -f2)
D=$(echo "$REGIONS" | cut -d, -f3)
V=$(echo "$REGIONS" | cut -d, -f4)
if [ "$V" -ge 80000 ]; then
    check_item "   Regions" "✅ P:$P R:$R D:$D V:$V"
else
    check_item "   Regions" "⚠️ P:$P R:$R D:$D V:$V (target: 35/514/7215/80532)"
fi
echo ""

# 15. Deploy.yml (sudo -u www-data)
echo "15. Deploy Script:"
if grep -q "sudo -u www-data" /var/www/devanka-logistik/.github/workflows/deploy.yml 2>/dev/null; then
    check_item "   Deploy" "✅ Uses sudo -u www-data"
else
    check_item "   Deploy" "⚠️ Not using sudo -u www-data"
fi
echo ""

echo "========================================"
echo "  SUMMARY: ✅ $PASS passed | ❌ $FAIL failed | ⚠️ $WARN warnings"
echo "========================================"