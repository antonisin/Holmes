#!/bin/sh

sleep 2
cd /var/www/api
php bin/console doctrine:database:create
php bin/console doctrine:schema:update -f
php-fpm

exec "$@"