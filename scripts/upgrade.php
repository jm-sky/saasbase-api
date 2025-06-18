#!/bin/bash

CONTAINER=${1:-saasbase-api-saasbase-1}

echo "📦 Target container: $CONTAINER"

echo "🔧 Installing Composer dependencies..."
docker exec -it "$CONTAINER" composer install

echo "🧱 Running database migrations..."
docker exec -it "$CONTAINER" php artisan migrate

echo "♻️ Clearing caches..."
docker exec -it "$CONTAINER" php artisan config:clear
docker exec -it "$CONTAINER" php artisan cache:clear
docker exec -it "$CONTAINER" php artisan route:clear
docker exec -it "$CONTAINER" php artisan view:clear

echo "🚀 Optimizing application..."
docker exec -it "$CONTAINER" php artisan optimize

echo "✅ Done."
