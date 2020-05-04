<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use OpenDialogAi\ConversationBuilder\Conversation;

class UpdateConversations extends Command
{
    protected $signature = 'conversations:update {conversation}';

    protected $description = 'Update a specific conversation';

    public function handle()
    {
        $conversationName = $this->argument('conversation');

        $continue = $this->confirm(
            sprintf(
                'This will update %s conversation. Are you sure you want to continue?',
                $conversationName
            )
        );

        if ($continue) {
            $this->importConversation($conversationName);

            $this->info('Imports finished');
        } else {
            $this->info('OK, not running');
        }
    }

    protected function importConversation($conversationName): void
    {
        $this->info(sprintf('Importing conversation %s', $conversationName));

        $model = file_get_contents("resources/conversations/$conversationName");

        $newConversation = Conversation::firstOrNew(['name' => $conversationName]);
        $newConversation->fill(['model' => $model]);
        $newConversation->save();

        $this->info(sprintf('Activating conversation with name %s', $newConversation->name));
        $newConversation->activateConversation();
    }
}
