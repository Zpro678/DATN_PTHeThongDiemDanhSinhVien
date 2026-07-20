# =============================================================================
#  Image PHP-FPM cho môi trường PRODUCTION.
#  Dùng bởi service `app` trong docker-compose.yml. Caddy đứng trước, nói
#  chuyện với container này qua FastCGI cổng 9000.
#
#  Stage 1 build asset (Vite/Tailwind) -> Stage 2 runtime PHP.
# =============================================================================

# ---------- Stage 1: build asset frontend ----------
FROM node:20-alpine AS assets

WORKDIR /build

COPY package.json package-lock.json ./
RUN npm ci

COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY resources ./resources
RUN npm run build

# ---------- Stage 2: runtime PHP ----------
FROM php:8.2-fpm-alpine

# --- Thư viện hệ thống cần cho các extension PHP ---
#  libpng/libjpeg/freetype : GD  -> intervention/image (avatar, ảnh minh chứng)
#  libzip                  : zip -> maatwebsite/excel (import/export)
#  icu                     : intl -> định dạng ngày giờ tiếng Việt
#  mysql-client            : mysqldump -> lệnh db:backup
RUN apk add --no-cache \
        bash \
        git \
        icu-dev \
        freetype-dev \
        libjpeg-turbo-dev \
        libpng-dev \
        libzip-dev \
        mysql-client \
        oniguruma-dev \
        supervisor \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        exif \
        gd \
        intl \
        opcache \
        pcntl \
        pdo_mysql \
        zip \
    && apk del .build-deps

# --- Composer ---
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# --- Cấu hình PHP cho production ---
RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.enable_cli=0'; \
        echo 'opcache.memory_consumption=192'; \
        echo 'opcache.max_accelerated_files=20000'; \
        echo 'opcache.validate_timestamps=0'; \
        echo 'upload_max_filesize=32M'; \
        echo 'post_max_size=32M'; \
        echo 'memory_limit=512M'; \
    } > /usr/local/etc/php/conf.d/zz-app.ini

# --- Mã nguồn ---
# Ở compose production thư mục dự án được mount đè vào /var/www/html, nên bước
# COPY này chủ yếu phục vụ build image độc lập (CI / registry).
COPY . /var/www/html

# Asset đã build ở stage 1 (public/build/manifest.json)
COPY --from=assets /build/public/build /var/www/html/public/build

RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader \
    && chown -R www-data:www-data storage bootstrap/cache

# --- Supervisor: chạy php-fpm + queue worker + scheduler trong 1 container ---
COPY docker/php/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
