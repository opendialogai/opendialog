#!/usr/bin/env bash

set -e
errormsg() { echo >&2 "Unfortunately there was an error, please try re-running or a manual installation."; }
trap errormsg EXIT

if [[ $# -eq 0 ]]; then
  echo "Please provide project name"
  exit $?
fi

PROJECT_NAME=$1

echo "Setting up the OD application"

echo "Installing dependencies..."
composer install

if test -f ".env"; then
    echo "Laravel env file already exists, not re-creating"
else
    echo "Setting up .env file"
    cp .env.example .env;
    sed -i -e "s/APP_NAME=.*/APP_NAME=\"$PROJECT_NAME\"/g" .env

    php artisan key:generate;

    echo "Updating app url"
    sed -i -e "s/http:\/\/localhost/http:\/\/$PROJECT_NAME.test/g" .env

    echo "Setting up the webchat widget..."
    bash update-web-chat.sh -iy
fi

echo "Setting up the database..."
php artisan migrate

echo "Populating default webchat settings..."
php artisan webchat:setup

echo "Application level config files"
php artisan vendor:publish --tag=od-config

echo "Creating example conversations..."
php artisan conversations:setup  --non-interactive

echo "Setting up the admin interface..."
yarn install
yarn run dev

echo "Creating a new user"
php artisan admin_user:create

echo
echo "The admin console is available here: http://$PROJECT_NAME.test/admin"
echo "You may login with the credentials admin@example.com / opendialog"
echo
echo "Finished! Now you may go to: http://$PROJECT_NAME.test/admin"

trap '' EXIT
