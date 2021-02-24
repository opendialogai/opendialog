# Upgrade guide

## 0.7.x to 1.0.x

### OpenDialog Components

A new concept of OpenDialog Components has been introduced to provide consistency for returning data to the administrator user interface. Actions, Attribute Types, Attributes, Contexts, Formatters Interpreters, Operations and Sensors are all considered OpenDialog Components. This means that we can ensure that each component defines an ID, a type and a source, as well as an optional human-readable name and description.

We've had the concept of ID's in 0.7, however it wasn't consistent and for the most part was a `$name` property, handled with the `HasName` trait. All usage of `$name` in components should be updated to a static `$componentId` property, and use of the `HasName` trait should no longer be presumed. The type and source of custom components will be automatically defined by core, so there is no need to account for these.

If you'd like to provide a human-readable name and description for your component, you'll need to define `$componentName` and `$componentDescription` static properties.

#### Contexts

Inline with the introduction of components, the way contexts are defined has become stricter. Previously it was possible to programmatically create a new context with `ContextSerivce::createContext('my_custom_context')`. As components require static properties, this programmatic creation is no longer supported and therefore `BaseContext` has also been removed. If you require a custom context you can create one by extending `AbstractCustomContext` and registering your context in the context engine configuration.

#### Operations

As it was necessary to provide information on the required attributes and parameters for operations there have been changes to how operations are defined. The `getAllowedParameters` method has been removed and replaced with a static `requiredParametersArgumentNames` array property. If no property is provided it will default to presuming a required `value` parameter argument.

Similarly, there is now a static `requiredAttributeArgumentNames` array property which can be used to define the required attributes for an operation. If no property is provided it will default to presuming a required `attribute` attribute argument.

This means that if your application has a custom operation that requires an input attribute argument called `attribute` and an input parameter argument called `value`, then you will only need to remove the `getAllowedParameters` (unless you wish to explicitly restate the defaults).

### Attribute Engine

OpenDialog core now has a separate Attribute Engine for managing attributes. Previously the Context Engine managed attributes as well as contexts, but a clearer separation of concerns has now been made. This update requires the follow changes to your application:

- The `AttributeResolver` facade should now be imported from `OpenDialogAi\AttributeEngine\Facades` rather than `OpenDialogAi\ContextEngine\Facades`.
- All core attribute types (`StringAttribute`, `IntAttribute`, etc) should now be imported from `OpenDialogAi\AttributeEngine\Attributes` rather than `OpenDialogAi\Core\Attribute`.
- The `context_engine.php` config file should no longer define `custom_attributes`. Run `php artisan vendor:publish --tag=opendialog-config` to publish the new `attribute_engine.php` and move your `custom_attributes` configuration there.
- If you use any custom attribute types, you'll now need to register these under `custom_attribute_types` in the new `attribute_engine.php`.

## DGraph v1.25 to v20.11.0

DGraph v20.11.0 requires authentication for access to **admin** routes. See (See https://dgraph.io/docs/deploy/dgraph-administration/#securing-alter-operations).
OpenDialog now sends an *auth token* in a header on all requests to dgraph.
 * For local development: Set matching values for the `DGRAPH_AUTH_TOKEN` environment variable in the opendialog `.env` file AND the opendialog-dev-environment `.env` file.
 * For existing deployments: Update the `dgraph alpha <...>` server command to include `--whitelist 0.0.0.0/0 --auth_token=<auth_token>`. Set a matching `DGRAPH_AUTH_TOKEN` environment variable in the opendialog environment.

DGraph *v20.11.0*'s data storage format is incompatible with *v1.25*. You must export your data **BEFORE** updating to *v20.11.0*. (See (https://dgraph.io/docs/deploy/dgraph-administration/#upgrading-database))
* For local development you can follow these steps:
    * Login to dgraph server:`docker-compose exec dgraph-server bash`
    * Export data: `curl localhost:8080/admin/export`
    * `ls -l` should show an `export` directory.
    * Copy export to host machine: `docker cp $(docker-compose ps -q dgraph-server):/dgraph/export export`
    * Shut down containers: `docker-compose down`
    * Update `opendialog-dev-environment` `.env` file with `DGRAPH_VERSION=v20.11.0`
    * Edit `opendialog-dev-environment` `docker-compose.yml` so that the `dgraph-server` volumes reads:
      ```
      volumes:
          - ${DATA_PATH_HOST}/dgraph/server_temp:/dgraph
      ```
      and the `dgraph-zero` volume reads
      ```
      volumes:
      - ${DATA_PATH_HOST}/dgraph/zero_temp:/dgraph
      ```
    * Start dgraph-zero, dgraph-server and ratel:
      `docker-compose up -d dgraph-zero dgraph-server ratel`

    * Copy export to dgraph-zero:
        * `docker cp export $(docker-compose ps -q dgraph-zero):/dgraph`
    * Connect to dgraph zero:
    * `docker-compose exec dgraph-zero bash`
    * Run dgraph live to import the database:
      `dgraph live -f export/<something>/g01.rdf.gz -s export/something/g01.schema.gz -a dgraph-server:9080 -t dgraphauthsecrettoken`
      Note the **'dgraphauthsecrettoken'** should match the `DGRAPH_AUTH_TOKEN` in the `opendialog-dev-environment` `.env` file.
    * Go to ratel in browser and check that the expected data is in dgraph: http://localhost:9001/?latest


    
    
