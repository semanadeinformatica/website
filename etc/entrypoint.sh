#!/bin/sh

set -e

# Cache config and other stuff
php artisan optimize -n
rm .env.${LARAVEL_ENV}*

/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
