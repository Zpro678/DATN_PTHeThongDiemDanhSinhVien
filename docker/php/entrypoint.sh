#!/bin/sh
# =============================================================================
#  Entrypoint container `app` (production).
#  Chạy các bước khởi tạo bắt buộc TRƯỚC khi bật php-fpm/worker.
# =============================================================================
set -e

cd /var/www/html

echo "[entrypoint] Chờ MySQL sẵn sàng..."
until php -r "new PDO('mysql:host='.getenv('DB_HOST').';port='.getenv('DB_PORT'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));" 2>/dev/null; do
    sleep 2
done

# Quyền ghi cho storage & cache (thư mục được mount từ host nên phải set lại)
chown -R www-data:www-data storage bootstrap/cache || true

# Liên kết public/storage (ảnh minh chứng xin nghỉ, avatar)
[ -L public/storage ] || php artisan storage:link || true

echo "[entrypoint] Chạy migration..."
php artisan migrate --force

echo "[entrypoint] Seed dữ liệu gói dịch vụ (idempotent)..."
php artisan db:seed --class=PlanSeeder --force

echo "[entrypoint] Nạp cache cấu hình..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "[entrypoint] Sẵn sàng."
exec "$@"
