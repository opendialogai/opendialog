<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;

class UpdateSchema extends Command
{
    protected $signature = 'schema:update';

    protected $description = 'Update local dgraph schema';

    public function handle()
    {
        $continue = $this->confirm(
            'This will update your local dgraph schema. Are you sure you want to continue?'
        );

        if ($continue) {
            $client = app()->make(DGraphClient::class);

            $this->info('Init Schema');
            $client->initSchema();

            $this->info('Schema updated');
        } else {
            $this->info('OK, not running');
        }
    }
}
