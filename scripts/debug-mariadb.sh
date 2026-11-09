#!/bin/bash
echo "=== MariaDB Debug ==="
echo "File exists:"
ls -la /etc/mysql/mariadb.conf.d/99-tuning.cnf 2>&1
echo "File content:"
cat /etc/mysql/mariadb.conf.d/99-tuning.cnf 2>&1
echo "grep test:"
grep -q "innodb_buffer_pool_size" /etc/mysql/mariadb.conf.d/99-tuning.cnf 2>&1 && echo "GREP OK" || echo "GREP FAILED"
echo "=== git status ==="
cd /var/www/devanka-logistik && git log --oneline -3
echo "=== check script line 74-82 ==="
sed -n '74,82p' scripts/check-phase1.sh