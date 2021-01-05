<?php

namespace App\Console\Commands\Specification;

use App\ImportExportHelpers\ConversationImportExportHelper;
use App\ImportExportHelpers\IntentImportExportHelper;
use App\ImportExportHelpers\MessageImportExportHelper;
use Illuminate\Console\Command;

class ImportSpecification extends Command
{
    protected $signature = 'specification:import {--y|yes} {--a|activate}';

    protected $description = 'Imports an entire application specification, conversations, intents and messages.';

    public function handle()
    {
        if ($this->option('yes')) {
            $continue = true;
        } else {
            $continue = $this->confirm('Do you want to remove all conversations, intents, messages and import the new ones?');
        }

        if ($continue) {
            MessageImportExportHelper::deleteExistingMessages($this);
            IntentImportExportHelper::deleteExistingIntents($this);
            ConversationImportExportHelper::deleteExistingConversations($this);

            $this->call(
                'conversations:import',
                [
                    '--yes' => true,
                    '--activate' => ($this->option('activate')) ? true : false,
                ]
            );

            $this->call(
                'intents:import',
                [
                    '--yes' => true
                ]
            );

            $this->call(
                'messages:import',
                [
                    '--yes' => true
                ]
            );
        }
    }
}
