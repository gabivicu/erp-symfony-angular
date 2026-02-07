#!/bin/sh
set -e

echo "Waiting for database..."
until php bin/console doctrine:query:sql "SELECT 1" >/dev/null 2>&1; do
  sleep 2
done
echo "Database is up."

# Generate JWT keys if missing
if [ ! -f config/jwt/private.pem ]; then
  php bin/console lexik:jwt:generate-keypair --overwrite --no-interaction 2>/dev/null || true
fi

php bin/console doctrine:migrations:migrate -n --allow-no-migration 2>/dev/null || true

exec php -S 0.0.0.0:8000 -t public
