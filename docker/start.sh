#!/bin/sh
set -e

cd /var/www/html

# Cache Laravel config/routes for performance (safe to fail on first boot before .env is ready)
php artisan config:cache || true
php artisan route:cache || true

# Start nginx + php-fpm together
exec supervisord -c /etc/supervisor/conf.d/supervisord.conf
