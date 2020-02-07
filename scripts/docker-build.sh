#!/usr/bin/env bash

USER="${DOCKER_USER}"
PASSWD="${DOCKER_PASS}"

PROJECT_NAME=${DOCKER_PROJECT_NAME}
TAG=$(git rev-parse --abbrev-ref HEAD | sed 's/\//_/g')

echo "Building docker image tagged with branch name - ${TAG}"

docker build . --tag=${PROJECT_NAME}:${TAG}

echo "Logging into docker"

docker login --username ${USER} --password ${PASSWD}

echo "Pushing to docker hub"

docker push opendialogai/opendialog:${TAG}

echo "Finished"
