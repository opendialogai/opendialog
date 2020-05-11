## Docker

### Builds

This project has been set up to automatically build a new docker image and push to the docker hub as part of the build process with CircleCI.
For this to work, the project must be set up in CircleCI and the following environment variables need to be set:

`DOCKER_BUILD` (must be set to true)
`DOCKER_USER`
`DOCKER_PASS`
`DOCKER_PROJECT_NAME`

A new docker build is not triggered unless all phpunit tests pass and the node build runs successfully.

The `tag` used for the new docker image is the current git branch name (with and `/` characters replaced with `_`)

### Running Docker locally

To run OpenDialog from a docker image, you should use the `docker-compose.yml` file included with this project along side the 
`env.docker` file. Any environment variables placed in this file will be used by the application. The `TAG` variable sets which image tag to use.

Changes to this file will not be picked up when the docker container is currently running - to see the changes made reflected in the application, run 

    docker-compose up -d app
    
#### Initial set up / updates

After first run, or to update a running application, the `docker-update.sh` script needs to be run. To run this within in the app container, run the following:

    docker-compose exec app bash docker/od-demo/update-docker.sh
    
This will run all database migration files, set up the webchat settings, optionally load all conversations and create the default admin user (if not already created)
