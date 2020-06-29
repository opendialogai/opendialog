
[![CircleCI](https://circleci.com/gh/opendialogai/opendialog/tree/master.svg?style=svg&circle-token=aefbfc509382266413d6667a1aef451c7bf82f22)](https://circleci.com/gh/opendialogai/opendialog/tree/master)

# OpenDialog - open-source conversational application management system 

OpenDialog enables you to quickly build conversational applications. 

It provides a web widget that can be styled to specific needs and can be embedded on any website. 

You write conversational applications using OpenDialog's flexible conversational language, and define the messages that your bot will send the user through OpenDialog's message markup language. 

For all the details of how OpenDialog helps you build sophisticated chatbots visit our [documentation site](https://docs.opendialog.ai).

# Trying out OpenDialog

If you want to see OpenDialog in action you can try out the latest version through our automatically produced Docker image. 

As long as you have Docker installed on your local machine you can do:
- `cd docker/od-demo`
- `docker-compose up -d app`
- `docker-compose exec app bash docker/od-demo/update-docker.sh`

You can then visit http://localhost and login to OpenDialog with admin@example.com / opendialog

# Learning about OpenDialog

To find out more about how OpenDialog works and a guide to building your first conversational application with OpenDialog visit our [docs website](https://docs.opendialog.ai). 

# Developing with OpenDialog

To setup a development environment for OpenDialog please check out the [OpenDialog development environment repository](https://github.com/opendialogai/opendialog-dev-environment) - it provides step by step instructions for setting up a Docker-based dev environment.  

# Session Management

Use sessions are handled by the standard Laravel session management systems (as defined in your `.env` file). By default, this is set to `file` which will only work as expected when there is a single OpenDialog app server.
When there are multiple app servers behind a load balancer, switch to using `redis` or `database` as your session management tool.
More info can be found in the [Laravel Session docs](https://laravel.com/docs/7.x/session)

# Continuous Integration

This project comes with a [CircleCI](http://www.circleci.com) (config.yml) file that sets up a basic workflow for the app that:
+ Runs all phpunit tests and stores the code coverage result
+ Attempts to install and build the project's Node dependencies
+ If all of these pass, builds a docker image and sends to docker hub.

For this to run successfully, you will need to set up CircleCI to watch the project in GitHub and add the following environment variables:

+ `DOCKER_BUILD` - `true`|`false`
+ `DOCKER_USER` - your Docker username
+ `DOCKER_PASS` - your Docker password
+ `DOCKER_PROJECT_NAME` - the full project name in DockerHub (eg `opendialogai/opendialog`)

## Docker tag names

By default, the `scripts/docker-build.sh` script will use either the current branch name, or the tag name if there is one associated with the current commit.
Branch names will have `/` replaced with `_` to meet Docker naming convention.

## GitHub Token during docker build

If you OpenDialog project has a composer dependency on a private GitHub repo, a local auth.json file can be created and used during the docker build.
To do this, just add a value for the environment variable `GITHUB_TOKEN` in CircleCI 