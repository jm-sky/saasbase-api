#!/bin/bash

SERVICE=${1:-saasbase} # <- domyślnie "saasbase", czyli nazwa usługi z docker-compose.yml

echo "🔧 Pullimg latest changes..."

git pull

echo "📦 Target service: $SERVICE (via docker compose exec)"

docker compose down 

echo "🔧 Installing Composer dependencies..."
docker compose exec "$SERVICE" composer install

echo "🧱 Running database migrations..."
docker compose exec "$SERVICE" php artisan migrate

echo "♻️ Clearing caches..."
docker compose exec "$SERVICE" php artisan config:clear
docker compose exec "$SERVICE" php artisan cache:clear
docker compose exec "$SERVICE" php artisan route:clear
docker compose exec "$SERVICE" php artisan view:clear

echo "🚀 Optimizing application..."
docker compose exec "$SERVICE" php artisan optimize

echo "✅ Done."
