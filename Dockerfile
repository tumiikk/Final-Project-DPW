# ============================================================
# Image untuk deployment di Railway: PHP 8.3 + Apache.
# Railway otomatis memakai Dockerfile ini bila ada di root repository.
# ============================================================
FROM php:8.3-apache

# Ekstensi PHP: pdo_mysql (koneksi MySQL) dan zip (export Excel .xlsx).
# fileinfo, mbstring, dan session sudah tersedia di image resmi.
RUN apt-get update \
    && apt-get install -y --no-install-recommends libzip-dev \
    && docker-php-ext-install pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/*

# Apache: aktifkan mod_rewrite (.htaccess), document root = folder public/, izinkan .htaccess.
# Hanya mpm_prefork yang aktif (wajib untuk modul PHP; mencegah error "More than one MPM loaded").
RUN a2dismod mpm_event mpm_worker >/dev/null 2>&1 || true \
    && a2enmod mpm_prefork rewrite \
    && sed -ri 's#/var/www/html#/var/www/html/public#g' /etc/apache2/sites-available/000-default.conf \
    && printf '<Directory /var/www/html/public>\n    AllowOverride All\n    Require all granted\n</Directory>\n' > /etc/apache2/conf-available/undangan.conf \
    && a2enconf undangan \
    && echo 'ServerName localhost' >> /etc/apache2/apache2.conf

# Pengaturan PHP production + batas upload (foto 5 MB, lagu 15 MB)
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && printf 'upload_max_filesize=16M\npost_max_size=20M\nexpose_php=Off\n' > "$PHP_INI_DIR/conf.d/undangan.ini"

WORKDIR /var/www/html
COPY . /var/www/html

# Salinan file upload bawaan project, dipulihkan ke Volume saat container pertama kali berjalan
RUN mkdir -p /opt/seed-storage && cp -a public/storage/. /opt/seed-storage/ \
    && chown -R www-data:www-data public/storage storage

ENV APP_ENV=production APP_DEBUG=false LOG_CHANNEL=stderr

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh
CMD ["/usr/local/bin/entrypoint.sh"]
