#!/usr/bin/env bash

if [[ $# -eq 0 ]]; then
  echo "Please provide project name"
  exit $?
fi

PROJECT_NAME=$1
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"

echo "Stopping any currently running docker containers to avoid port clashes"
docker stop $(docker ps -aq)

# Clone into local dir
if [[ -d "${DIR}/../opendialog-development-environment" ]]; then
  echo "Dev environment already exists, not cloning again"
else
    echo "Cloning dev environment locally"
    git clone https://github.com/opendialogai/opendialog-dev-environment.git ${DIR}/../opendialog-development-environment
fi

# Create an Nginx Conf
if test -f "opendialog-development-environment/nginx/sites/${PROJECT_NAME}.conf"; then
    echo "NGINX conf exists, not recreating"
else
    echo "Creating NGINX conf"
    cp opendialog-development-environment/nginx/sites/opendialog.conf.example opendialog-development-environment/nginx/sites/${PROJECT_NAME}.conf

    echo "Modifying Nginx conf for project"
    sed -i -e "s/root \/var\/www\/opendialog\/public/root \/var\/www\/public/g" opendialog-development-environment/nginx/sites/${PROJECT_NAME}.conf
    sed -i -e "s/opendialog/${PROJECT_NAME}/g" opendialog-development-environment/nginx/sites/${PROJECT_NAME}.conf
    rm -f opendialog-development-environment/nginx/sites/${PROJECT_NAME}.conf-e
fi

# Add to unix hosts file
if [[ `grep "${PROJECT_NAME}.test" /etc/hosts` ]]; then
    echo "Hosts file contains an entry for this project, please confirm manually in /etc/hosts"
else
    echo "Adding to hosts file"
    echo "127.0.0.1 ${PROJECT_NAME}.test" >> /etc/hosts
fi

# Copy the laradock .env file
if test -f "opendialog-development-environment/.env"; then
        echo ".env exists, not recreating"
else
    echo "Copying .env file"
    cp opendialog-development-environment/env.example opendialog-development-environment/.env
fi

# Add to unix hosts file
if [[ `grep "${PROJECT_NAME}.test" /etc/hosts` ]]; then
    echo "Hosts file contains an entry for this project, please confirm manually in /etc/hosts"
else
    echo "Adding to hosts file"
    echo "127.0.0.1 ${PROJECT_NAME}.test" >> /etc/hosts
fi

echo "Updating .env to be project specific"
sed -i -e "s/DATA_PATH_HOST=~\/.laradock\/opendialog\/data/DATA_PATH_HOST=~\/.laradock\/${PROJECT_NAME}\/data/g" opendialog-development-environment/.env
sed -i -e "s/COMPOSE_PROJECT_NAME=opendialog/COMPOSE_PROJECT_NAME=${PROJECT_NAME}/g" opendialog-development-environment/.env
rm -f opendialog-development-environment/.env-e


if test -f "opendialog-development-environment/.env-e"; then
    rm opendialog-development-environment/.env-e
fi

echo "Starting the dev containers"
cd opendialog-development-environment
bash scripts/start.sh

echo "Setting up OD"
docker-compose exec workspace bash scripts/set-up-od.sh $1