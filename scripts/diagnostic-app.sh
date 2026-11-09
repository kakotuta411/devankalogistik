#!/bin/bash
# Diagnostic: Region sync source, Redis, OPcache, DB indexes

echo "========================================"
echo "  DIAGNOSTIC: APP PERFORMANCE & SECURITY"
echo "  $(date)"
echo "========================================"
echo ""

# 1. Region sync source
echo "1. REGION SYNC SOURCE:"
cd /var/www/devanka-logistik
echo "   Commands:"
ls app/Console/Commands/ 2>/dev/null | grep -i "region\|sync" || echo "   No sync command found"
echo "   URL/API references:"
grep -rn "http\|api\|url\|emsifa\|wilayah\|fetch\|curl" app/Console/Commands/ 2>/dev/null | head -15 || echo "   No references found"
echo ""

# 2. Redis status
echo "2. REDIS:"
if command -v redis-server &>/dev/null; then
    echo "   redis-server: $(redis-server --version 2>/dev/null)"
else
    echo "   redis-server: NOT installed"
fi
if command -v redis-cli &>/dev/null; then
    redis-cli ping 2>/dev/null && echo "   redis-cli: running" || echo "   redis-cli: not running"
else
    echo "   redis-cli: NOT installed"
fi
php -r "echo '   PHP Redis ext: ' . (extension_loaded('redis') ? 'YES' : 'NO') . PHP_EOL;" 2>/dev/null
echo ""

# 3. OPcache status
echo "3. OPCACHE:"
php -i 2>/dev/null | grep -i "opcache.enable\b" | head -3
php -i 2>/dev/null | grep -i "opcache.memory_consumption" | head -1
php -i 2>/dev/null | grep -i "opcache.max_accelerated_files" | head -1
php -i 2>/dev/null | grep -i "opcache.validate_timestamps" | head -1
echo ""

# 4. DB indexes
echo "4. DATABASE INDEXES:"
sudo mysql -e "
SELECT TABLE_NAME, COLUMN_NAME, INDEX_NAME, NON_UNIQUE
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = 'devanka_logistik'
ORDER BY TABLE_NAME, SEQ_IN_INDEX;
" 2>/dev/null | head -60
echo ""

# 5. Missing indexes (foreign keys without index)
echo "5. MISSING INDEXES (FK without index):"
sudo mysql -e "
SELECT k.TABLE_NAME, k.COLUMN_NAME, k.CONSTRAINT_NAME
FROM information_schema.KEY_COLUMN_USAGE k
LEFT JOIN information_schema.STATISTICS s
  ON k.TABLE_SCHEMA = s.TABLE_SCHEMA
  AND k.TABLE_NAME = s.TABLE_NAME
  AND k.COLUMN_NAME = s.COLUMN_NAME
WHERE k.TABLE_SCHEMA = 'devanka_logistik'
  AND k.REFERENCED_TABLE_NAME IS NOT NULL
  AND s.INDEX_NAME IS NULL;
" 2>/dev/null
echo "   (empty = no missing indexes)"
echo ""

# 6. Table sizes
echo "6. TABLE SIZES:"
sudo mysql -e "
SELECT TABLE_NAME, TABLE_ROWS, ROUND(DATA_LENGTH/1024/1024,2) AS 'Data MB', ROUND(INDEX_LENGTH/1024/1024,2) AS 'Index MB'
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'devanka_logistik'
ORDER BY DATA_LENGTH DESC;
" 2>/dev/null
echo ""

# 7. PHP config
echo "7. PHP CONFIG:"
php -i 2>/dev/null | grep -i "upload_max_filesize\|post_max_size\|memory_limit\|max_execution_time" | head -5
echo ""

echo "========================================"
echo "  DIAGNOSTIC COMPLETE"
echo "========================================"