#!/bin/bash

set -e

echo "Setting up the database..."
php artisan migrate --force

echo "Populating default webchat settings..."
php artisan webchat:setup

echo "Initializing dgraph schema..."
php artisan schema:init --yes

echo "Creating admin user"
php artisan user:create {}

echo "Creating default component configurations"
php artisan configurations:create

echo "Ensuring log directory is writable..."
chmod -R 777 /var/www/storage/logs
