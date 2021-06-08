

# OpenDialog - open-source conversational application platform

OpenDialog enables you to quickly design, develop and deploy conversational applications. 

You write conversational applications using OpenDialog's flexible no-code conversation designer and define the messages that your bot will send the user through OpenDialog's message editor.  

<img src="https://www.opendialog.ai/wp-content/uploads/2021/04/od_intro2-1.gif" width="585px" alt="OpenDialog Designer Intro">

The OpenDialog webchat widget allows you to interact with the application - it supports both an in-page popup experience as well as a full-page experience and mobile. 

<img src="https://www.opendialog.ai/wp-content/uploads/2021/04/webchat_images.png" alt="OpenDialog Webchat Widget">

Behind the scenes this all gets translated into the OpenDialog Conversation Description Language and gets run through our powerful conversation engine, giving you flexible, sophisticated chat experiences everytime. 

For all the details of how OpenDialog helps you build sophisticated conversation applications visit our [documentation site](https://docs.opendialog.ai).


# Trying out OpenDialog

If you want to see OpenDialog in action you can try out the latest version through our automatically produced Docker image. 

As long as you have Docker installed on your local machine you can do:
- `cd od-docker-demo`
- `docker-compose up -d app`
- `docker-compose exec app bash docker/scripts/update-docker.sh`

You can then visit http://localhost and login to OpenDialog with admin@example.com / opendialog - you can also view the full page webchat experience on http://localhost/web-chat

There are more detailed instructions in readme the `od-docker-demo` directory

# Learning about OpenDialog

To find out more about how OpenDialog works and a guide to building your first conversational application with OpenDialog visit our [docs website](https://docs.opendialog.ai). 

Read our [OpenDialog Manifesto](https://www.opendialog.ai/manifesto) which captures our views on what is at the core of conversational applications and what the most important design principles are. These ideas underpin our vision for OpenDialog.

# Developing with OpenDialog

To setup a development environment for OpenDialog please check out the [OpenDialog development environment repository](https://github.com/opendialogai/opendialog-dev-environment) - it provides step by step instructions for setting up a Docker-based dev environment.

# Contributing to OpenDialog

We strongly encourage everyone who wants to help the OpenDialog development take a look at the following resources:
- CONTRIBUTING.md
- CODE_OF_CONDUCT.md
- Take a look at our issues

# License
Licensed under the [Apache License, Version 2.0](https://github.com/opendialogai/opendialog/blob/1.x/LICENSE.txt). Copyright 2021 OpenDialog.

A list of the Licenses of the dependencies of the project can be found at the bottom of the Libraries Summary.

