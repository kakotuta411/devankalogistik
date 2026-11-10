#!/bin/bash
# Setup rclone Backblaze B2 + off-site backup cron
#
# Pakai:
#   sudo B2_KEY_ID=xxx B2_APP_KEY=yyy B2_BUCKET=devanka-backup bash setup-rclone.sh
#
# B2_KEY_ID  = keyID dari Backblaze B2
# B2_APP_KEY = applicationKey dari Backblaze B2
# B2_BUCKET  = nama bucket (default: devanka-backup)

set -e

B2_KEY_ID="${B2_KEY_ID:?harus set B2_KEY_ID}"
B2_APP_KEY="${B2_APP_KEY:?harus set B2_APP_KEY}"
B2_BUCKET="${B2_BUCKET:-devanka-backup}"

echo "=== 1. Install rclone (kalau belum) ==="
if ! command -v rclone >/dev/null 2>&1; then
    curl https://rclone.org/install.sh | sudo bash
else
    echo "rclone sudah terinstall: $(rclone version | head -1)"
fi

echo ""
echo "=== 2. Tulis config rclone ==="
mkdir -p /root/.config/rclone
cat > /root/.config/rclone/rclone.conf << EOF
[devanka-b2]
type = b2
account = ${B2_KEY_ID}
key = ${B2_APP_KEY}
EOF
chmod 600 /root/.config/rclone/rclone.conf
echo "Config ditulis ke /root/.config/rclone/rclone.conf"

echo ""
echo "=== 3. Test koneksi ==="
if rclone lsd "devanka-b2:${B2_BUCKET}" >/dev/null 2>&1; then
    echo "Bucket '${B2_BUCKET}' bisa diakses ✓"
else
    echo "⚠️  Bucket '${B2_BUCKET}' belum ada / belum bisa diakses."
    echo "   Buat dulu di dashboard Backblaze B2, atau jalankan:"
    echo "   rclone mkdir devanka-b2:${B2_BUCKET}"
fi

echo ""
echo "=== 4. Pasang cron off-site backup (tiap hari 05:00) ==="
cat > /etc/cron.d/offsite-backup << EOF
0 5 * * * root rclone sync /backup devanka-b2:${B2_BUCKET} --quiet 2>&1 | tee -a /var/log/rclone-backup.log
EOF
chmod 644 /etc/cron.d/offsite-backup
echo "Cron dipasang: /etc/cron.d/offsite-backup"

echo ""
echo "=== Selesai ==="
echo "Test manual: sudo rclone ls devanka-b2:${B2_BUCKET}"
echo "Log backup : /var/log/rclone-backup.log"
