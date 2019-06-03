#!/bin/bash
set -e
errormsg() { echo >&2 "Unfortunately there was an error, please try re-running or a manual installation."; }
trap errormsg EXIT

# Ensure that lando is installed.
command -v lando >/dev/null 2>&1 || { echo >&2 "Please install lando: https://docs.devwithlando.io/installation/system-requirements.html"; exit 1; }

echo "Adding Laravel environment settings..."
cp -n .env.example.lando .env || echo "A .env file was already present, not copying example..."

echo "Starting services..."
lando start

echo "Installing dependencies..."
lando composer install

echo "Setting up the webchat widget..."
bash update-web-chat.sh

echo "Setting up the database..."
lando artisan migrate

echo "Populating default webchat settings..."
lando artisan webchat:setup

echo "Application level config files"
lando artisan vendor:publish --tag=od-config

echo "Creating example conversations..."
lando artisan conversations:setup

echo "Setting up the admin interface..."
npm install
npm run dev
lando ssh --service database --command 'mysql -uroot laravel -e '"'"'INSERT INTO users (name, email, password, created_at, updated_at) VALUES ("admin", "admin@example.com", "$2y$10$BEhBWA12KObSY9Ua2G0VeOg2hWMT1GIa8huHD83HCEHnJLnRcH8w6", NOW(), NOW())'"'"' '

echo
echo "The admin console is available here: https://opendialog.lndo.site/od-admin"
echo "You may login with the credentials admin@example.com / opendialog"
echo
echo "Finished! Now you may go to: https://opendialog.lndo.site/demo"

trap '' EXIT
