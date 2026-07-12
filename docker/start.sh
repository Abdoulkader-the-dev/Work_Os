#!/bin/sh

ROLE=${CONTAINER_ROLE:-web}

if [ "$ROLE" = "web" ]; then
    echo "Starting Web Server..."
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
    php artisan storage:link
    # Start nginx in foreground (keeps container alive)
    exec nginx -g "daemon off;"
elif [ "$ROLE" = "reverb" ]; then
    echo "Starting Reverb Server..."
    exec php artisan reverb:start --host="0.0.0.0" --port="${PORT:-8080}"
else
    echo "Unknown CONTAINER_ROLE: $ROLE"
    exit 1
fi
