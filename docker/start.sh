#!/bin/sh

# Replace ${PORT} in nginx config with the actual PORT env variable
envsubst '${PORT}' < /etc/nginx/nginx.conf > /etc/nginx/nginx.conf.tmp && mv /etc/nginx/nginx.conf.tmp /etc/nginx/nginx.conf

# Run migrations (idempotent — safe to run on every deploy)
php artisan migrate --force

# Cache Laravel for production performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start PHP-FPM in background
php-fpm -D

# Start nginx in foreground (keeps container alive)
nginx -g "daemon off;"
