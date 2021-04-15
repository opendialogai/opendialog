

# OpenDialog - open-source conversational application platform

OpenDialog enables you to quickly design, develop and deploy conversational applications. 

You write conversational applications using OpenDialog's flexible no-code conversation designer and define the messages that your bot will send the user through OpenDialog's message editor.  

The OpenDialog webchat widget allows you to interact with the application - it supports both an in-page popup experience as well as a full-page experience and mobile. 

Behind the scenes this all gets translated into the OpenDialog Conversation Description Language and gets run through our powerful conversation engine, giving you flexible, sophisticated chat experiences everytime. 

<img src="https://www.opendialog.ai/wp-content/uploads/2021/04/webchat_images.png" alt="OpenDialog Webchat Widget">

For all the details of how OpenDialog helps you build sophisticated conversation applications visit our [documentation site](https://docs.opendialog.ai).

<img src="https://www.opendialog.ai/wp-content/uploads/2021/04/od_intro2-1.gif" width="585px" alt="OpenDialog Designer Intro">


# Trying out OpenDialog

If you want to see OpenDialog in action you can try out the latest version through our automatically produced Docker image. 

As long as you have Docker installed on your local machine you can do:
- `cd docker/od-demo`
- `cp .env.example .env`
- `docker-compose up -d app`
- `docker-compose exec app bash scripts/update-docker.sh`

You can then visit http://localhost and login to OpenDialog with admin@example.com / opendialog - you can also view the full page webchat experience on http://localhost/web-chat

# Learning about OpenDialog

To find out more about how OpenDialog works and a guide to building your first conversational application with OpenDialog visit our [docs website](https://docs.opendialog.ai). 

# Developing with OpenDialog

To setup a development environment for OpenDialog please check out the [OpenDialog development environment repository](https://github.com/opendialogai/opendialog-dev-environment) - it provides step by step instructions for setting up a Docker-based dev environment.  

