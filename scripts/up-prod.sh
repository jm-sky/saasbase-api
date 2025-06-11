#!/bin/bash

# Source the production environment file
if [ -f ./.env.production ]; then
  source ./.env.production
elif [ -f ./.env ]; then
  source ./.env
fi

# Define environment variables
export APP_PORT=${APP_PORT:-80}
export APP_SERVICE=${APP_SERVICE:-"saasbase"}
export APP_USER=${APP_USER:-"www-data"}
export DB_PORT=${DB_PORT:-5432}
export WWWUSER=${WWWUSER:-$UID}
export WWWGROUP=${WWWGROUP:-$(id -g)}

# Determine if we should use docker compose or docker-compose
if docker compose &> /dev/null; then
    DOCKER_COMPOSE=(docker compose -f docker-compose.prod.yml)
else
    DOCKER_COMPOSE=(docker-compose -f docker-compose.prod.yml)
fi

# Run docker compose up with the provided arguments
"${DOCKER_COMPOSE[@]}" up "$@"