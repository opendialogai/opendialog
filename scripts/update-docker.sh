#!/bin/bash

set -e

echo "Setting up the database..."
php artisan migrate --force

echo "Creating admin user"
php artisan admin_user:create

echo "Ensuring log directory is writable..."
chmod -R 777 /var/www/storage/logs
