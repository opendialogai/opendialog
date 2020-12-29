<?php

namespace App\Console\Commands\Specification;

use App\ImportExportHelpers\ConversationImportExportHelper;
use Illuminate\Console\Command;
use OpenDialogAi\ConversationBuilder\Conversation;

class ExportConversations extends Command
{
    protected $signature = 'conversations:export {conversation?} {--y|yes} {--a|active}';

    protected $description = 'Exports all conversations.';

    public function handle()
    {
        $conversationName = $this->argument('conversation');

        if ($this->option('yes') || $this->option('y')) {
            $continue = true;
        } elseif ($conversationName) {
            $continue = $this->confirm(
                sprintf(
                    'Do you want to export conversation %s?',
                    $conversationName
                )
            );
        } else {
            $continue = $this->confirm('Do you want to export all conversations?');
        }

        if ($continue) {
            if ($conversationName) {
                $conversation = Conversation::where('name', $conversationName)->first();

                if (is_null($conversation)) {
                    $this->error(sprintf('%s doesn\'t exist.', $conversationName));
                    return;
                } else {
                    $this->exportConversation($conversation);
                }
            } else {
                $activeConversations = config('opendialog.active_conversations');

                $conversations = Conversation::all();

                foreach ($conversations as $conversation) {
                    if (!$this->option('active') || in_array($conversation->name, $activeConversations)) {
                        $this->exportConversation($conversation);
                    }
                }
            }

            $this->info('Export of conversations finished');
        } else {
            $this->info('Bye');
        }
    }

    protected function exportConversation(Conversation $conversation): void
    {
        $this->info(sprintf('Exporting conversation %s', $conversation->name));

        $conversationFileName = ConversationImportExportHelper::addConversationFileExtension($conversation->name);
        ConversationImportExportHelper::createConversationFile($conversationFileName, $conversation->model);
    }
}
