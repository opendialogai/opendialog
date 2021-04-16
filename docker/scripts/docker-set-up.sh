#!/bin/bash

echo "Installing dependencies..."
composer install --no-dev

echo "Application level config files"
php artisan vendor:publish --tag=od-config

echo "Setting up the admin interface..."
npm install -g yarn
yarn install
yarn run prod
