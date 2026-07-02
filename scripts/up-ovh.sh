#!/bin/bash

# Skrypt uruchamiający SaaSBase na serwerze OVH z RustFS (bez MinIO)

# Source the ".env" file
if [ -f ./.env ]; then
  source ./.env
else
  echo "Error: .env file not found!"
  exit 1
fi

# Define environment variables
export APP_PORT=${APP_PORT:-8989}
export APP_SERVICE=${APP_SERVICE:-"app"}
export APP_USER=${APP_USER:-"sail"}
export DB_PORT=${DB_PORT:-5432}
export WWWUSER=${WWWUSER:-$UID}
export WWWGROUP=${WWWGROUP:-$(id -g)}

# Determine if we should use docker compose or docker-compose
if docker compose &> /dev/null; then
    DOCKER_COMPOSE=(docker compose)
else
    DOCKER_COMPOSE=(docker-compose)
fi

# Run docker compose with OVH configuration
"${DOCKER_COMPOSE[@]}" -f docker-compose.ovh.yml --env-file .env up "$@"
