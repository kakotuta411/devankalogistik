#!/bin/bash
# Phase 1 Fix Script
# Fixes: unnecessary services, region sync re-run, MariaDB verification

echo "========================================"
echo "  PHASE 1 FIX SCRIPT"
echo "  $(date)"
echo "========================================"
echo ""

# ============================================
# 1. Identify & Disable Remaining Services
# ============================================
echo "1. Checking remaining unnecessary services..."
REMAINING=$(systemctl list-unit-files --state=enabled 2>/dev/null | grep -oE "acpid|ModemManager|multipathd|udisks2|upower|apport|pollinate|thermald|gpu-manager|sysstat|inetutils-inetd" | sort -u)
echo "   Found: $REMAINING"
echo ""

for svc in $REMAINING; do
    echo "   Disabling $svc..."
    sudo systemctl disable --now "$svc" 2>/dev/null && echo "   ✅ $svc disabled" || echo "   ⚠️ $svc could not be disabled"
done
echo ""

# ============================================
# 2. Verify MariaDB Config
# ============================================
echo "2. Verifying MariaDB config..."
echo "   Config file:"
cat /etc/mysql/mariadb.conf.d/99-tuning.cnf 2>/dev/null | head -5
echo ""
echo "   Active setting:"
sudo mysql -e "SHOW VARIABLES LIKE 'innodb_buffer_pool_size';" 2>/dev/null || echo "   ⚠️ Could not query (need auth)"
echo ""

# ============================================
# 3. Re-run Region Sync
# ============================================
echo "3. Re-running region sync..."
cd /var/www/devanka-logistik
sudo -u www-data php artisan regions:sync 2>&1 | tail -5
echo ""

# ============================================
# 4. Verify Region Count
# ============================================
echo "4. Region counts after sync:"
sudo -u www-data php artisan tinker --execute="
echo 'Provinces: '.\App\Models\Region::where('level','province')->count().PHP_EOL;
echo 'Regencies: '.\App\Models\Region::where('level','regency')->count().PHP_EOL;
echo 'Districts: '.\App\Models\Region::where('level','district')->count().PHP_EOL;
echo 'Villages: '.\App\Models\Region::where('level','village')->count().PHP_EOL;
" 2>/dev/null
echo ""

echo "========================================"
echo "  FIX COMPLETE"
echo "========================================"