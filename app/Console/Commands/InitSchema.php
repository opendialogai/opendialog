<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;

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
            $client = app()->make(DGraphClient::class);

            $this->info('Dropping Schema');
            $client->dropSchema();

            $this->info('Init Schema');
            $client->initSchema();

            $this->info('Schema initialized');
        } else {
            $this->info('OK, not running');
        }
    }
}
