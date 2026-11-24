#!/bin/sh
# Dijalankan setiap container Railway mulai:
# 1. Apache mendengarkan port dari variabel PORT yang diberikan Railway.
# 2. File upload bawaan disalin ke Volume (tanpa menimpa file yang sudah ada).
# 3. Tabel database, akun admin, dan draft default disiapkan (aman diulang).
# 4. Apache dijalankan.
set -e

PORT="${PORT:-8080}"
sed -ri "s/^Listen [0-9]+/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

mkdir -p /var/www/html/public/storage
cp -an /opt/seed-storage/. /var/www/html/public/storage/ 2>/dev/null || true
chown -R www-data:www-data /var/www/html/public/storage /var/www/html/storage

# Database Railway bisa butuh beberapa detik untuk siap: coba hingga 10 kali
i=1
until php /var/www/html/cli/install.php; do
    if [ "$i" -ge 10 ]; then
        echo "Instalasi database gagal. Periksa variabel koneksi MySQL di Railway." >&2
        break
    fi
    echo "Menunggu database siap... ($i)"
    i=$((i + 1))
    sleep 5
done

exec apache2-foreground
