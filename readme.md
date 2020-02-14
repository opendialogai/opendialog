
[![CircleCI](https://circleci.com/gh/opendialogai/opendialog/tree/master.svg?style=svg&circle-token=aefbfc509382266413d6667a1aef451c7bf82f22)](https://circleci.com/gh/opendialogai/opendialog/tree/master)

# OpenDialog Demo

This is a sample application that pulls in the [Open Dialog core](https://github.com/opendialogai/core) and [Open Dialog Webchat](https://github.com/opendialogai/webchat/) packages and provides a demonstration of the OpenDialog platform with webchat. 

# Set Up Instructions

## Quickstart

This will get you up and running with minimal manual configuration.

* [Install Lando](https://docs.devwithlando.io/installation/system-requirements.html) -- [Lando](https://github.com/lando/lando) is a wrapper around Docker services and it brings together everything that is required for OpenDialog.
 
* Run the setup script: `bash ./scripts/set_up_od.sh {appname}` where {appname} is the name of the app
On initial setup you will be prompted to clear your local dgraph and conversations. Select `yes`.
* Your app will be available at: https://{appname}.lndo.site/admin
    * You may need to permanently trust the ssl cert
    * You can find this at `~/.lando/certs/lndo.site.crt`
* Log in using default credentials `admin@example.com` and `opendialog`
* Go to > Test Bot
    * Ask the Bot anything.
    * You should see the no-match message.
* The DGraph browser will be available here: https://dgraph-ratel.lndo.site
* DGraph Alpha should be available at https://dgraph-alpha.lndo.site

## Manual Configuration

### Front end set up

After running `composer install` or `composer update`, an update script file should be moved to the root of your project
directory. Run this script to set up the OpenDialogAI-Webchat and OpenDialogAI-Core packages with

```bash update-web-chat.sh -i```

#### Options

The following options are available on the script:

+ `-h` Get help
+ `-p` Set if this is to be run in the production environment
+ `-l` Set if you are using Lando for local development. Will run the commands from within Lando
+ `-i` Set if you need to install the node dependencies. This defaults to false, so you should always set this for the fist run
+ `-f` Whether to force updating by deleting local dependencies. If set, will remove the vue-beautiful-chat node module before reinstalling 

Run this script every time an underlying package is updated.

#### Webchat Configuration 

The webchat configuration can be found in the `webchat_settings` table. The config table should be seeded by running:

```php artisan webchat:setup```

This will set up the `webchat_settings` table with all the requried values.
For this to work successfully, the `APP_URL` environment variable need to be set

#### DGraph configuration

Add (and edit as necessary) the following lines to your .env file to let OD know where to find your DGraph installation:
```
DGRAPH_URL=http://dgraph-alpha
DGRAPH_PORT=8080
```

(`http://dgraph-alpha` is the internally resolvable hostname for DGraph in the lando set up)

#### Config

Publish the opendialog config by running:

```php artisan vendor:publish --tag=opendialog-config```

This will copy over all required config files to `config/opendialog` for you to add you own values


## Conversations

Conversations in OpenDialog are managed in the mysql database, and published to DGraph when they are ready to be used.

There are 2 scripts included with this application that allow you to import and export conversations that can be checked into 
the repo and shared

### Configuration

There is a config file `opendialog/active_conversations.php` in the config directory. This contains a list of all conversation
names that should be exported / imported. This list is used by both scripts and should be kept up to date with your local conversations.
Just the conversation name is needed.

### Import Conversations

To import all conversations, run

```php artisan conversations:setup```

This will import all conversations that are listed in `opendialog/active_conversations.php` and exist in `resources/conversartions`

#### Example Conversations

By default, the welcome and no match conversations are included with OpenDialog. Running the script will create a no match
and a welcome conversation (but without the required messages)

### Exporting Conversations

Run:

```php artisan conversations:export```

To export all conversations in the `opendialog/active_conversations.php` config file. This will dump the current conversation
YAML and all related outgoing intents and message templates

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

    docker-compose exec app bash scripts/update-docker.sh
    
This will run all database migration files, set up the webchat settings, optionally load all conversations and create the default admin user (if not already created)

## Local Package Development

The `packages:install` artisan command will checkout and symlink `opendialog-core` and / or `opendialog-webchat` to a `vendor-local` directory.

To install dependencies using it, you can run `artisan packages:install`. You will be asked if you want to use local versions of core and webchat.
If so, you can now use, edit and version control these repositories directly from your `vendor-local` directory.

After doing so, you may need to run `php artisan package:discover` to pick up any new modules.

Note:
Before a final commit for a feature / fix, please be sure to run `composer update` to update the `composer-lock.json` file so that it can be tested and deployed with all composer changes in place

### Reverting

To revert back to the dependencies defined in `composer.json`, run the `artisan packages:install` command again and answer no to installing core and webchat locally.

## Testing

The project is set up to run all commits through (CircleCI)[https://circleci.com], which runs tests and checks for code 
standards.

To run the test suite locally through Lando, run 

    lando test

Information on setting up phpstorm to run tests on the (OpenDialog Wiki)[https://github.com/opendialogai/opendialog/wiki/Running-tests-through-PHPStorm]

## Running Code Sniffer
To run code sniffer, run the following command
```./vendor/bin/phpcs --standard=od-cs-ruleset.xml app/ --ignore=*/migrations/*,*/tests/*```

## Git Hooks

To set up the included git pre-commit hook, first make sure the pre-commit script is executable by running

```chmod +x .githooks/pre-commit```

Then configure your local git to use this directory for git hooks by running:

```git config core.hooksPath .githooks/```

Now every commit you make will trigger php codesniffer to run. If there is a problem with the formatting
of the code, the script will echo the output of php codesniffer. If there are no issues, the commit will
go into git.
