#!/bin/bash
set -e

echo "==> Configuring Nginx site for Laravel public directory..."
cp /home/site/wwwroot/azure/nginx.conf /etc/nginx/sites-available/default
service nginx reload

cd /home/site/wwwroot

echo "==> Linking Storage..."
php artisan storage:link --force

echo "==> Caching Configurations, Routes, and Views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Running Database Migrations & Seeders..."
php artisan migrate --force
php artisan db:seed --force

echo "==> Starting Laravel Reverb WebSocket Server in Background..."
php artisan reverb:start --host=127.0.0.1 --port=8081 > /home/site/wwwroot/storage/logs/reverb.log 2>&1 &

echo "==> Starting Background Queue Worker..."
php artisan queue:work --sleep=3 --tries=3 --max-time=3600 > /home/site/wwwroot/storage/logs/queue.log 2>&1 &

echo "==> Startup Completed Successfully."

