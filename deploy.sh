#!/bin/bash

cd ~/orcei

# Turn on maintenance mode
docker compose exec -T app php artisan down || true

# Pull the latest changes from the git repository
git pull origin main

# Install composer dependecies
docker compose exec -T app composer install --no-interaction --no-dev --prefer-dist --optimize-autoloader

# Run database migrations
docker compose exec -T app php artisan migrate --force

# Optimize view, routes, events, config
docker compose exec -T app php artisan optimize:clear
docker compose exec -T app php artisan filament:optimize-clear
docker compose exec -T app php artisan optimize
docker compose exec -T app php artisan filament:optimize

# Restart horizon with the updated code
docker compose exec -T supervisor php artisan horizon:terminate

# Turn off maintenance mode
docker compose exec -T app php artisan up

exit 0
