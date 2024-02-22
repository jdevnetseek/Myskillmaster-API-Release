#!/bin/bash
# Change to the project directory
cd /var/www/api

# Turn on maintenance mode
php artisan down || true

# Run database migrations
php artisan migrate --force

# Clear caches
php artisan cache:clear

# Clear expired password reset tokens
php artisan auth:clear-resets

# Clear and cache routes
php artisan route:cache

# Clear and cache config
php artisan config:cache

# Clear and cache views
php artisan view:cache

# Storage Link
php artisan storage:link

# Terminate Horizon
php artisan horizon:terminate

# Turn off maintenance mode
php artisan up