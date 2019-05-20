
[![CircleCI](https://circleci.com/gh/opendialogai/opendialog/tree/master.svg?style=svg&circle-token=aefbfc509382266413d6667a1aef451c7bf82f22)](https://circleci.com/gh/opendialogai/opendialog/tree/master)

# OpenDialog Demo
This is a sample application that pulls in the [Open Dialog core](https://github.com/opendialogai/core) and [Open Dialog Webchat](https://github.com/opendialogai/webchat/) packages and provides a demonstration of the OpenDialog platform with webchat. 

It additionally provides an administrative interface (currently leveraging [Laravel Nova](https://nova.laravel.com) to manage conversations, intents & messages<sup id="nova-note">[1](#f1)</sup>.

# Set Up Instructions

## Quickstart
This will get you up and running with minimal manual configuration.

* [Install](https://docs.devwithlando.io/installation/system-requirements.html) [lando](https://github.com/lando/lando)
 -- Lando is a wrapper around Docker services and it brings together everything that is required for OpenDialog.
 
* Add the auth.json file for Laravel Nova (See the "Nova installation" heading in this document)
* Run the setup script: `bash ./scripts/set_up_od.sh`
* Now you can go to: https://opendialog.lndo.site/demo
* You should see the no-match message 
* The DGraph browser will be available here: http://dgraph-ratel.lndo.site/?latest

## Manual Configuration

### Front end set up

After running `composer install` or `composer update`, an update script file should be moved to the root of your project
directory. Run this script to set up the OpenDialogAI-Webchat and OpenDialogAI-Core packages with

```bash update-web-chat.sh```

Run this script every time an underlying package is updated.

#### Webchat Configuration 

The webchat configuration can be found in the `webchat_settings` table. The config table should be seeded by running:

```php artisan webchat:setup```

This will set up the `webchat_settings` table with all the requried values.
For this to work successfully, the `APP_URL` environment variable need to be set

### DGraph configuration

#### Running DGraph

If you don't have a `dgraph` directory in the root of your project, run

```php artisan vendor:publish --tag=dgraph```

to copy it over from the OpenDialogAi-Core package.

Now follow the instructions found in `dgraph-setup.md`

#### Config

Add (and edit as necessary) the following lines to your .env file to let OD know where to find your DGraph installation:
```
DGRAPH_URL=http://10.0.2.2
DGRAPH_PORT=8080
```

These settings should work out of the box if you are using Laravel Homestead. More info in `draph/dgraph-setup.md`

### Example Conversations

To set up with example conversations, run 

```php artisan conversations:setup```

This will create a no match and a welcome conversation (but without the required messages)

### Nova installation

This package makes use of [Laravel Nova](https://nova.laravel.com) for backend administration.

It is added as a composer requirement, but to install, you will need to add your Laravel Nova credentials to a composer
auth file named `auth.json` at the root of your project in the following format:

```json
    {
      "http-basic": {
        "nova.laravel.com": {
          "username": "{username}",
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

### Local dev

A `composer-dev.json` file has been created to help with local development. It makes the assumption that you have the 
Open Dialog and Open Dialog Webchat packages checked out locally to `../OpenDialog` and `../OpenDialog-Webchat`
respectively.

To install dependencies using it, you can run `./composer-dev install` or `./composer-dev update`

After doing so, you may need to run `php artisan package:discover` to pick up any new modules.

Note:

+ Any changes made in `composer-dev.json` must be reflected in `composer.json` and vice versa
+ Before a final commit for a feature / fix, please be sure to run `composer update` to update the `composer-lock.json`
file so that it can be tested and deployed with all composer changes in place

### Running Code Sniffer
To run code sniffer, run the following command
```./vendor/bin/phpcs --standard=psr12 app/ nova-components/*/src/```

--- 
<a name="nova-note">1</a>: We are conscious of the fact that Laravel Nova is an open-source but paid for product and are working towards a replacement. For the time being, however, it offers a quick solution while we focus on the underlying engine issues!
