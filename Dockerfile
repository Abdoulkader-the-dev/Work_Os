# Stage 1: Node (build assets)
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm ci || npm install
COPY . .
RUN npm run build

# Stage 2: Composer (PHP dependencies)
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-scripts --no-autoloader
COPY . .
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative

# Stage 3: Final image
FROM php:8.3-fpm-alpine

# OS Dependencies
RUN apk add --no-cache \
    nginx \
    postgresql-dev \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    gettext \
    icu-dev \
    oniguruma-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql gd zip bcmath pcntl mbstring exif intl

WORKDIR /var/www/html

# Copy application files (but avoid overwriting with node_modules or vendor if excluded via .dockerignore)
COPY . .

# Copy vendor from Stage 2
COPY --from=vendor /app/vendor/ /var/www/html/vendor/

# Copy built frontend assets from Stage 1
COPY --from=frontend /app/public/build/ /var/www/html/public/build/

# Set Permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Config Nginx
COPY docker/nginx.conf /etc/nginx/nginx.conf

# Start script
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

ENV PORT=10000
EXPOSE 10000

CMD ["/start.sh"]
