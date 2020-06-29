#!/bin/bash

set -e

if [[ -z "${APP_KEY}" ]]; then
    echo "Generating key..."
    php artisan key:generate
else
    echo "APP_KEY variable defined"
fi

echo "Ensuring log directory is writable..."
chmod -R 777 /var/www/storage/logs

# Fix for cURL error 35: error:141A318A:SSL routines:tls_process_ske_dhe:dh key too small
sed -i 's/DEFAULT@SECLEVEL=2/DEFAULT@SECLEVEL=1/g' /etc/ssl/openssl.cnf

service nginx start
php-fpm
