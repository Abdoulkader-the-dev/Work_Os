#!/bin/bash
set -e

npm install
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
