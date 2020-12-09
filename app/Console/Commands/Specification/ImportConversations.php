<?php

namespace App\Console\Commands\Specification;

use App\ImportExportHelpers\ConversationImportExportHelper;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use OpenDialogAi\ConversationBuilder\Conversation;
use OpenDialogAi\Core\Conversation\Conversation as ConversationNode;

class ImportConversations extends Command
{
    protected $signature = 'conversations:import {conversation?} {--y|yes} {--activate|activate}';

    protected $description = 'Import all conversations';

    public function handle()
    {
        $conversationName = $this->argument('conversation');

        if ($this->option('yes')) {
            $continue = true;
        } elseif ($conversationName) {
            $continue = $this->confirm(
                sprintf(
                    'Do you want to import conversation %s?',
                    $conversationName
                )
            );
        } else {
            $continue = $this->confirm(
                'This will import or update all conversations. Are you sure you want to continue?'
            );
        }

        if ($continue) {
            $activate = ($this->option('activate')) ? true : false;

            if ($conversationName) {
                $this->importConversation($conversationName . '.conv', $activate);
            } else {
                $files = ConversationImportExportHelper::getConversationFiles();

                foreach ($files as $conversationFileName) {
                    $this->importConversation($conversationFileName, $activate);
                }
            }

            $this->info('Import of conversations finished');
        } else {
            $this->info('OK, not running');
        }
    }

    protected function importConversation($conversationFileName, $activate): void
    {
        $conversationName = ConversationImportExportHelper::getConversationNameFromFileName($conversationFileName);

        $this->info(sprintf('Importing conversation %s', $conversationName));

        try {
            $model = ConversationImportExportHelper::getConversationFileData($conversationFileName);
        } catch (FileNotFoundException $e) {
            $this->error(sprintf('Could not find conversation at %s', $conversationFileName));
            return;
        }

        $newConversation = Conversation::firstOrNew(['name' => $conversationName]);
        $newConversation->status = ConversationNode::SAVED;
        $newConversation->version_number = 0;
        $newConversation->graph_uid = null;
        $newConversation->fill(['model' => $model]);
        $newConversation->save();

        if ($activate) {
            $this->info(sprintf('Activating conversation with name %s', $newConversation->name));
            $newConversation->activateConversation();
        }
    }
}
