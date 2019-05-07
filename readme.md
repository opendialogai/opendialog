# OpenDialog
This is the package that will hold all of the OpenDialog code and logic.

It pulls in both Open Dialog core and Open Dialog Webchat Package

# Set Up Instructions

## Quickstart
* [Install](https://docs.devwithlando.io/installation/system-requirements.html) [lando](https://github.com/lando/lando)
* `composer install`
* `bash update-web-chat.sh`
* `cp .env.example.lando .env`
* `lando start`
* `lando artisan migrate`
* `lando artisan webchat:setup`
* `lando artisan conversations:setup`
* `lando artisan nova:publish`
* `lando artisan nova:user`
* Go to: http://opendialog.lndo.site/admin, log in with the user you just created, and create outgoing intents for your conversations:
** First, create an outgoing intent with the name intent.core.NoMatchResponse, and then create a message template from its page:
```
name: no match
conditions: <empty>
Message Mark-up:
<message>
<text-message>I'm sorry, but I don't understand</text-message>
</message>
```
** First, create an outgoing intent with the name intent.opendialog.welcome_response, and then create a message template from its page:
```
name: no match
conditions: <empty>
Message Mark-up:
<message>
<text-message>Hi there!</text-message>
</message>
```
* Go to: http://opendialog.lndo.site/demo
* The DGraph browser will be available here: http://dgraph-ratel.lndo.site/?latest

## Front end set up

After running `composer install` or `composer update`, an update script file should be moved to the root of your project
directory. Run this script to set up the OpenDialogAI-Webchat and OpenDialogAI-Core packages with

```bash update-web-chat.sh```

Run this script every time an underlying package is updated.

### Webchat Configuration 

The webchat configuration can be found in the `webchat_settings` table. The config table should be seeded by running:

```php artisan webchat:setup```

This will set up the `webchat_settings` table with all the requried values.
For this to work successfully, the `APP_URL` environment variable need to be set

## DGraph configuration

### Running DGraph

If you don't have a `dgraph` directory in the root of your project, run

```php artisan vendor:publish --tag=dgraph```

to copy it over from the OpenDialogAi-Core package.

Now follow the instructions found in `dgraph-setup.md`

### Config

Add (and edit as necessary) the following lines to your .env file to let OD know where to find your DGraph installation:
```
DGRAPH_URL=http://10.0.2.2
DGRAPH_PORT=8080
```

These settings should work out of the box if you are using Laravel Homestead. More info in `draph/dgraph-setup.md`

## Example Conversations

To set up with example conversations, run 

```php artisan conversations:setup```

This will create a no match and a welcome conversation (but without the required messages)

## Nova installation

This package makes use of [Laravel Nova](https://nova.laravel.com) for backend administration.

It is added as a composer requirement, but to install, you will need to add your Laravel Nova credentials to a composer
auth file named `auth.json` at the root of your project in the following format:

```json
    {
      "http-basic": {
        "nova.laravel.com": {
          "username": "{usernbame}",
          "password": "{password}"
        }
      }
    }
```

Make sure to install Nova with

``` php artisan nova:install```

To create a user to let you log into the Nova pages, run 

```php artisan nova:user```

Once this has run, you can access Nova by navigating to: ```{APP_URL}/admin``` 

## Local dev

A `composer-dev.json` file has been created to help with local development. It makes the assumption that you have the 
Open Dialog and Open Dialog Webchat packages checked out locally to `../OpenDialog` and `../OpenDialog-Webchat`
respectively.

To install dependencies using it, you can run `./composer-dev install` or `./composer-dev update`

After doing so, you may need to run `php artisan package:discover` to pick up any new modules.

Note:

+ Any changes made in `composer-dev.json` must be reflected in `composer.json` and vice versa
+ Before a final commit for a feature / fix, please be sure to run `composer update` to update the `composer-lock.json`
file so that it can be tested and deployed with all composer changes in place

## Running Code Sniffer
To run code sniffer, run the following command
```./vendor/bin/phpcs --standard=psr12 app/ nova-components/*/src/```
