#!/bin/bash

echo "Installing dependencies..."
composer install --no-dev

echo "Setting up the webchat widget..."
bash ./update-web-chat.sh -pify

echo "Application level config files"
php artisan vendor:publish --tag=od-config

echo "Setting up the admin interface..."
npm install -g yarn
yarn install
yarn run prod
