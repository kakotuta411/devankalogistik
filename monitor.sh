#!/bin/bash
# monitor.sh — cek kesehatan server Devanka (Opsi B: extended)
# Alert via Slack webhook + log file
#
# Pasang cron:
#   echo '*/5 * * * * root /var/www/devanka-logistik/monitor.sh' | sudo tee /etc/cron.d/vps-monitor
#   sudo chmod 644 /etc/cron.d/vps-monitor

SLACK_WEBHOOK="${SLACK_WEBHOOK:-}"
LOG_FILE="/var/log/devanka-monitor.log"
APP_DIR="/var/www/devanka-logistik"

ALERTS=()
add_alert() { ALERTS+=("$1"); }

# 1. Site health
HTTP=$(curl -s -o /dev/null -w "%{http_code}" --max-time 10 https://devankalogistik.web.id/api/health 2>/dev/null || echo "000")
[ "$HTTP" != "200" ] && add_alert "Site down (HTTP $HTTP)"

# 2. DB connection
DB_OK=$(cd "$APP_DIR" && sudo -u www-data /usr/bin/php8.3 artisan tinker --execute="try { DB::connection()->getPdo(); echo 'ok'; } catch (Exception \$e) { echo 'fail'; }" 2>/dev/null | tail -1)
[ "$DB_OK" != "ok" ] && add_alert "DB connection failed"

# 3. Disk usage
DISK_USE=$(df / | tail -1 | awk '{print $5}' | tr -d '%')
[ "$DISK_USE" -gt 80 ] && add_alert "Disk ${DISK_USE}% (>80%)"

# 4. RAM usage
RAM_USE=$(free | grep Mem | awk '{printf "%.0f", $3/$2 * 100}')
[ "$RAM_USE" -gt 70 ] && add_alert "RAM ${RAM_USE}% (>70%)"

# 5. CPU load (threshold = 2 x core)
CPU_CORES=$(nproc)
LOAD_THRESHOLD=$((CPU_CORES * 2))
LOAD_NOW=$(awk '{print $1}' /proc/loadavg)
if awk "BEGIN {exit !($LOAD_NOW > $LOAD_THRESHOLD)}"; then
    add_alert "CPU load ${LOAD_NOW} (> ${LOAD_THRESHOLD} di ${CPU_CORES} core)"
fi

# 6. Services
for svc in nginx php8.3-fpm mariadb redis-server; do
    systemctl is-active --quiet "$svc" || add_alert "Service $svc mati"
done

# 7. SSL certificate (< 14 hari)
if ! echo | openssl s_client -servername devankalogistik.web.id -connect devankalogistik.web.id:443 2>/dev/null | openssl x509 -noout -checkend 1209600 >/dev/null 2>&1; then
    add_alert "SSL certificate < 14 hari expired"
fi

# 8. Backup DB terakhir < 24 jam
if ! find /backup -name "db_*.sql.gz" -mtime -1 2>/dev/null | grep -q .; then
    add_alert "Backup DB terakhir > 24 jam"
fi

# 9. Error baru di laravel.log (10 menit terakhir)
if find "$APP_DIR/storage/logs/laravel.log" -mmin -10 2>/dev/null | grep -q .; then
    add_alert "Ada error baru di laravel.log"
fi

# Kirim alert
if [ ${#ALERTS[@]} -gt 0 ]; then
    MESSAGE="[ALERT] Devanka VPS — $(date '+%Y-%m-%d %H:%M')"
    for a in "${ALERTS[@]}"; do MESSAGE="$MESSAGE
- $a"; done
    echo "$MESSAGE" >> "$LOG_FILE"

    if [ -n "$SLACK_WEBHOOK" ]; then
        PAYLOAD=$(printf '%s' "$MESSAGE" | python3 -c "import sys,json; print(json.dumps({'text': sys.stdin.read()}))")
        curl -s -X POST -H 'Content-type: application/json' --data "$PAYLOAD" "$SLACK_WEBHOOK" >/dev/null
    fi
else
    echo "[OK] $(date '+%Y-%m-%d %H:%M') — semua sehat" >> "$LOG_FILE"
fi
