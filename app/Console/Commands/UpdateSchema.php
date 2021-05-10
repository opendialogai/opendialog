<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use OpenDialogAi\GraphQLClient\DGraphGraphQLClient;
use OpenDialogAi\GraphQLClient\GraphQLClientInterface;

class UpdateSchema extends Command
{
    protected $signature = 'schema:update {--y|yes}';

    protected $description = 'Update Dgraph schema';

    public function handle()
    {
        if ($this->option('yes')) {
            $continue = true;
        } else {
            $continue = $this->confirm(
                'This will update your Dgraph schema. Are you sure you want to continue?'
            );
        }

        if ($continue) {
            /** @var DGraphGraphQLClient $client */
            $client = resolve(GraphQLClientInterface::class);

            $this->info('Update Schema');
            $client->setSchema(config('opendialog.graphql.schema'));
            $this->info('Schema updated');
        } else {
            $this->info('OK, not running');
        }
    }
}
