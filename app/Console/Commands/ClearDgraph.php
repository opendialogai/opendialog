<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use OpenDialogAi\GraphQLClient\DGraphGraphQLClient;
use OpenDialogAi\GraphQLClient\GraphQLClientInterface;

class ClearDgraph extends Command
{
    protected $signature = 'dgraph:clear {--y|yes}';

    protected $description = 'Clears down all DGraph data leaving schema in tact';

    public function handle()
    {
        if ($this->option('yes')) {
            $continue = true;
        } else {
            $continue = $this->confirm(
                'This will clear your local dgraph data. Are you sure you want to continue?'
            );
        }

        if ($continue) {
            /** @var DGraphGraphQLClient $client */
            $client = resolve(GraphQLClientInterface::class);
            $this->info('Clearing down DGraph data');
            $client->dropData();
            $this->info('Cleared');
        } else {
            $this->info('OK, not running');
        }

        return 0;
    }
}
