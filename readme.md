
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

