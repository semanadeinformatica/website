#!/bin/sh

set -e

# Clear stale config cache from build, then re-cache with current .env
php artisan config:clear -n
php artisan optimize -n

/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
