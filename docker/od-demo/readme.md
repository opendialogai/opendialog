# OpenDialog Prebuilt Docker Image

The OpenDialog CI process builds a docker image for every feature branch. This docker-compose configuration helps you to quickly get started with the latest build of OpenDialog from the opendialogai Docker hub [registry](https://hub.docker.com/repository/docker/opendialogai/opendialog).

## Running Docker locally

To run OpenDialog from a Docker image, you should use the `docker-compose.yml` file included with this project alongside the `.env.example` file in this directory.

First, copy the `.env.example` to `.env`. Any environment variables placed in this file will be used by the application. The `TAG` variable sets which image tag to use from the OpenDialog public DockerHub [repository](https://hub.docker.com/repository/registry-1.docker.io/opendialogai/opendialog/tags). This is set to `develop` by default.

Once you've created a `.env` file, to get the application up and running use: 

    `docker-compose up -d app`

After first run, or to update a running application, the `docker-update.sh` script needs to be run. To run this within in the app container, run the following:

    `docker-compose exec app bash docker/od-demo/update-docker.sh`
    
This will run all database migration files, set up the webchat settings, optionally load all conversations and create the default admin user (if not already created).

You can then visit `http://localhost` and you should be able to login to the OpenDialog application (user:admin@example.com \ password: opendialog)

If you need a newer copy of the same image, power down the app with
 
 `docker-compose down`

pull the new image

`docker pull opendialogai/opendialog:develop`

replacing dev with the tag you are interested in and then start it up again. 

` docker-compose up -d --force-recreate app`

## Including Docker builds in your CI process

If you want to build in your own CI process in  CircleCI and the following environment variables need to be set:

`DOCKER_BUILD` (must be set to true)
`DOCKER_USER`
`DOCKER_PASS`
`DOCKER_PROJECT_NAME`

A new docker build is not triggered unless all phpunit tests pass and the node build runs successfully.

The `tag` used for the new docker image is the current git branch name (with and `/` characters replaced with `_`)

    
