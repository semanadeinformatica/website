#!/bin/sh

set -e

# Cache config and other stuff
php artisan optimize -n

/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf