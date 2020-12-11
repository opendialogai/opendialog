#!/bin/bash

set -e

echo "Setting up the database..."
php artisan migrate --force

echo "Populating default webchat settings..."
php artisan webchat:setup

echo "Initializing dgraph schema..."
php artisan schema:init --yes

echo "Creating example conversations..."
php artisan conversations:setup --non-interactive

echo "Creating outgoing intents and message templates..."
php artisan messages:import --yes

echo "Creating admin user"
php artisan admin_user:create

echo "Ensuring log directory is writable..."
chmod -R 777 /var/www/storage/logs
