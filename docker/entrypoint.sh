#!/bin/sh
set -e
cd /var/www/html

# The uploads volume is mounted over storage/app/public, so fix its ownership on every start.
mkdir -p storage/app/public storage/framework/cache storage/framework/sessions storage/framework/views storage/logs
chown -R www-data:www-data storage bootstrap/cache

if [ -z "$APP_KEY" ]; then
  echo "APP_KEY is not set. Generate one with: php artisan key:generate --show" >&2
  exit 1
fi

# Wait for MySQL (Dokploy may start the app before the database is ready).
i=0
until php -r 'new PDO("mysql:host=".getenv("DB_HOST").";port=".(getenv("DB_PORT") ?: "3306").";dbname=".getenv("DB_DATABASE"), getenv("DB_USERNAME"), getenv("DB_PASSWORD"));' >/dev/null 2>&1; do
  i=$((i + 1))
  if [ "$i" -ge 30 ]; then echo "Database not reachable after 60s" >&2; exit 1; fi
  echo "Waiting for database..."
  sleep 2
done

php artisan migrate --force

# First boot only: create the admin user (ADMIN_EMAIL / ADMIN_PASSWORD) and import the site content.
# Later boots skip this so edits made in the admin panel are never overwritten.
USERS=$(php artisan tinker --execute='echo \App\Models\User::count();' 2>/dev/null | tail -n 1)
if [ "$USERS" = "0" ]; then
  echo "Empty database: seeding admin user and site content"
  php artisan db:seed --force
fi

php artisan storage:link --force >/dev/null 2>&1 || true
php artisan optimize
php artisan filament:optimize

exec "$@"
