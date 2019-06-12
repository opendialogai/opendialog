#!/usr/bin/env bash

cd vendor/opendialogai/webchat

rm -rf node_modules/vue-beautiful-chat

lando npm install

lando npm run dev

cd ../../../

rm -r public/vendor/webchat

lando php artisan vendor:publish --tag=public --force

lando php artisan vendor:publish --tag=vue-components --force
