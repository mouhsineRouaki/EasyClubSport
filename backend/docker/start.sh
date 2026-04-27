#!/bin/sh

set -e

ROLE="${1:-web}"
ARTISAN_ENV="${ARTISAN_ENV:-docker}"

echo "Attente de la base de donnees..."
until pg_isready -h "${DB_HOST:-db}" -p "${DB_PORT:-5432}" -U "${DB_USERNAME:-postgres}" >/dev/null 2>&1
do
  sleep 2
done

if [ ! -f /app/vendor/autoload.php ]; then
  echo "Installation des dependances Composer..."
  composer install --no-interaction
fi

mkdir -p storage/logs bootstrap/cache
chmod -R 775 storage bootstrap/cache || true

php artisan --env="${ARTISAN_ENV}" storage:link || true
php artisan --env="${ARTISAN_ENV}" migrate --force || true

if [ "$ROLE" = "queue" ]; then
  echo "Demarrage du worker de queue..."
  exec php artisan --env="${ARTISAN_ENV}" queue:listen --tries=1 --timeout=0
fi

if [ "$ROLE" = "reverb" ]; then
  echo "Demarrage de Reverb..."
  exec php artisan --env="${ARTISAN_ENV}" reverb:start --host=0.0.0.0 --port="${REVERB_SERVER_PORT:-8081}"
fi

echo "Demarrage du serveur Laravel..."
exec php artisan serve --env="${ARTISAN_ENV}" --no-reload --host=0.0.0.0 --port=8000
