#!/bin/bash

# Source the ".env" file so Laravel's environment variables are available
if [ -n "$APP_ENV" ] && [ -f ./.env."$APP_ENV" ]; then
  source ./.env."$APP_ENV"
elif [ -f ./.env ]; then
  source ./.env
fi

# Define environment variables
export APP_PORT=${APP_PORT:-80}
export APP_SERVICE=${APP_SERVICE:-"saasbase.test"}
export APP_USER=${APP_USER:-"sail"}
export DB_PORT=${DB_PORT:-3306}
export WWWUSER=${WWWUSER:-$UID}
export WWWGROUP=${WWWGROUP:-$(id -g)}

# Determine if we should use docker compose or docker-compose
if docker compose &> /dev/null; then
    DOCKER_COMPOSE=(docker compose)
else
    DOCKER_COMPOSE=(docker-compose)
fi

# Run docker compose up with the provided arguments
"${DOCKER_COMPOSE[@]}" up "$@"