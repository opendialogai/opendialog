# Upgrade file.

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


    
    
