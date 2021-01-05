<?php

namespace App\Console\Commands\Specification;

use App\ImportExportHelpers\ConversationImportExportHelper;
use App\ImportExportHelpers\IntentImportExportHelper;
use App\ImportExportHelpers\MessageImportExportHelper;
use Illuminate\Console\Command;

class ExportSpecification extends Command
{
    protected $signature = 'specification:export {--y|yes}';

    protected $description = 'Exports the entire specification, conversations, messages and intents.';

    public function handle()
    {
        if ($this->option('yes')) {
            $continue = true;
        } else {
            $continue = $this->confirm('Do you want to remove all conversations, intents, messages and export the new ones?');
        }

        if ($continue) {
            $conversationFiles = ConversationImportExportHelper::getConversationFiles();
            foreach ($conversationFiles as $conversationFile) {
                if (is_file($conversationFile)) {
                    ConversationImportExportHelper::deleteConversationFile($conversationFile);
                }
            }

            $intentFiles = IntentImportExportHelper::getIntentFiles();
            foreach ($intentFiles as $intentFile) {
                if (is_file($intentFile)) {
                    IntentImportExportHelper::deleteIntentFile($intentFile);
                }
            }

            $messageFiles = MessageImportExportHelper::getMessageFiles();
            foreach ($messageFiles as $messageFile) {
                if (is_file($messageFile)) {
                    MessageImportExportHelper::deleteMessageFile($messageFile);
                }
            }

            $this->call(
                'conversations:export',
                [
                    '--yes' => true
                ]
            );

            $this->call(
                'intents:export',
                [
                    '--yes' => true
                ]
            );

            $this->call(
                'messages:export',
                [
                    '--yes' => true
                ]
            );
        }
    }
}
