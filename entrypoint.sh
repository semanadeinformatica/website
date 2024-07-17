#!/bin/sh

php artisan db:migrate --force --seed --no-interaction --isolated
/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
