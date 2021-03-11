<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use OpenDialogAi\GraphQLClient\DGraphGraphQLClient;
use OpenDialogAi\GraphQLClient\GraphQLClientInterface;

class InitSchema extends Command
{
    protected $signature = 'schema:init {--y|yes}';

    protected $description = 'Init local dgraph schema';

    public function handle()
    {
        if ($this->option('yes')) {
            $continue = true;
        } else {
            $continue = $this->confirm(
                'This will clear your local dgraph schema. Are you sure you want to continue?'
            );
        }

        if ($continue) {
            /** @var DGraphGraphQLClient $client */
            $client = resolve(GraphQLClientInterface::class);

            $this->info('Init Schema');
            $client->setSchema(config('opendialog.graphql.schema'));
            $this->info('Schema initialized');
        } else {
            $this->info('OK, not running');
        }
    }
}
