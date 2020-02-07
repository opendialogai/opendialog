#!/bin/bash

echo "Generating key..."
php artisan key:generate

echo "Ensuring log directory is writable..."
chmod -R 777 /var/www/storage/logs

service nginx start
php-fpm
