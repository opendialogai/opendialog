#!/bin/bash

echo "Setting up the database..."
php artisan migrate

echo "Populating default webchat settings..."
php artisan webchat:setup

echo "Creating example conversations..."
php artisan conversations:setup

echo "Creating admin user"
php artisan admin_user:create

echo "Ensuring log directory is writable..."
chmod -R 777 /var/www/storage/logs
