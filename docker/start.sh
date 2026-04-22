#!/bin/sh
set -e

cd /var/www/html

if [ ! -f .env ]; then
  cp .env.example .env
fi

if [ ! -f vendor/autoload.php ]; then
  composer install --no-interaction --prefer-dist
fi

if [ -f package-lock.json ] && [ ! -f node_modules/.bin/vite ]; then
  npm ci --no-audit --no-fund
fi

if [ -f package.json ]; then
  npm run build
fi

if ! grep -q '^APP_KEY=base64:' .env; then
  php artisan key:generate --force
fi

mkdir -p \
  storage/framework/cache \
  storage/framework/cache/data \
  storage/framework/sessions \
  storage/framework/testing \
  storage/framework/views \
  storage/logs \
  bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache
find storage bootstrap/cache -type d -exec chmod 775 {} \;
find storage bootstrap/cache -type f -exec chmod 664 {} \;

ATTEMPT=1
MAX_ATTEMPTS=30

until php artisan migrate --force --seed; do
  if [ "$ATTEMPT" -ge "$MAX_ATTEMPTS" ]; then
    echo "Migration failed after ${MAX_ATTEMPTS} attempts"
    exit 1
  fi

  echo "Database is not ready yet, retry ${ATTEMPT}/${MAX_ATTEMPTS}..."
  ATTEMPT=$((ATTEMPT + 1))
  sleep 2
done

exec php-fpm
