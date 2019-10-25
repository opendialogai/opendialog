#!/bin/bash

set -e
errormsg() { echo >&2 "Unfortunately there was an error, please try re-running or a manual installation."; }
trap errormsg EXIT

# Ensure that lando is installed.
command -v lando >/dev/null 2>&1 || { echo >&2 "Please install lando: https://docs.devwithlando.io/installation/system-requirements.html"; exit 1; }

if [ $# -eq 0 ]
  then
    echo >&2 "Please supply the name of the application as the second argument"
    exit
fi

echo "Adding Laravel environment settings..."
cp -n .env.example.lando .env || echo "A .env file was already present, not copying example..."

echo "Updating name of app in .env file"
sed -i -e "s/APP_NAME=.*/APP_NAME=\"$1\"/g" .env

echo "Creating local Lando file"
cp -n .lando.yml.example .lando.yml || echo "A Lando file was already created, not copying example..."

echo "Updating name of app in .lando file"
name=`echo $1 | sed 's/ //g'`
sed -i -e "s/{appname}*/${name}/g" .lando.yml

echo "Updating app url"
sed -i -e "s/https:\/\/.*.lndo.site/https:\/\/$1.lndo.site/g" .env

echo "Starting services..."
lando start

echo "Installing dependencies..."
lando composer install

echo "Setting up the webchat widget..."
bash update-web-chat.sh -liy

echo "Setting up the database..."
lando artisan migrate

echo "Populating default webchat settings..."
lando artisan webchat:setup

echo "Application level config files"
lando artisan vendor:publish --tag=od-config

echo "Creating example conversations..."
lando artisan conversations:setup

echo "Generating key..."
lando php artisan key:generate

echo "Setting up the admin interface..."
lando yarn install
lando yarn run dev
lando ssh --service database --command 'mysql -uroot laravel -e '"'"'INSERT INTO users (name, email, password, api_token, created_at, updated_at) VALUES ("admin", "admin@example.com", "$2y$10$BEhBWA12KObSY9Ua2G0VeOg2hWMT1GIa8huHD83HCEHnJLnRcH8w6", "aa4eyvtfiundw6xu3jjgbmn6dzw51si34vlozci6bovy00h2j2a2dqk2d68b", NOW(), NOW())'"'"' '

echo
echo "The admin console is available here: https://$1.lndo.site/admin"
echo "You may login with the credentials admin@example.com / opendialog"
echo
echo "Finished! Now you may go to: https://$1.lndo.site/demo"

trap '' EXIT
