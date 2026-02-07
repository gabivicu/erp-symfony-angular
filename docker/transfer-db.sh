#!/bin/bash
# Transfer database from local PostgreSQL (port 5432) to Docker container
# Uses host.docker.internal to access localhost from Docker container

set -e

echo "🔄 Exporting database from local PostgreSQL..."

# Check if Docker containers are running
if ! docker compose ps database | grep -q "Up"; then
  echo "❌ Docker database container is not running. Starting it..."
  docker compose up -d database
  sleep 5
fi

# Use the database container to export from local PostgreSQL
# host.docker.internal allows container to access host's localhost
echo "Attempting to connect to local PostgreSQL (127.0.0.1:5432)..."

# Try different connection methods
if docker compose exec -T database \
  pg_dump -h host.docker.internal -p 5432 -U staystrong -d app \
  --clean --if-exists --no-owner --no-acl -F p > /tmp/db_export.sql 2>/dev/null; then
  echo "✅ Connected without password"
elif docker compose exec -T database \
  pg_dump -h host.docker.internal -p 5432 -U postgres -d app \
  --clean --if-exists --no-owner --no-acl -F p > /tmp/db_export.sql 2>/dev/null; then
  echo "✅ Connected as 'postgres' user"
else
  echo "❌ Could not connect automatically."
  echo ""
  echo "Please provide PostgreSQL password for user 'staystrong':"
  read -s PG_PASS
  export PGPASSWORD="$PG_PASS"
  docker compose exec -T -e PGPASSWORD="$PG_PASS" database \
    pg_dump -h host.docker.internal -p 5432 -U staystrong -d app \
    --clean --if-exists --no-owner --no-acl -F p > /tmp/db_export.sql
fi

echo "✅ Export completed: /tmp/db_export.sql"
echo "📊 File size: $(du -h /tmp/db_export.sql | cut -f1)"

echo ""
echo "🔄 Importing into Docker database..."

# Import into Docker container
docker compose exec -T database psql -U app -d app < /tmp/db_export.sql

echo "✅ Import completed!"
echo ""
echo "🧹 Cleaning up..."
rm /tmp/db_export.sql

echo "✅ Database transfer complete!"
