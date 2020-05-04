<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use OpenDialogAi\ConversationBuilder\Conversation;

class SetUpConversations extends Command
{
    protected $signature = 'conversations:setup {--y|yes}';

    protected $description = 'Sets up all active conversations';

    public function handle()
    {
        if ($this->option("yes")) {
            $continue = true;
        } else {
            $continue = $this->confirm(
                'This will import or update all active conversations. Are you sure you want to continue?'
            );
        }

        if ($continue) {
            $files = preg_grep('/^([^.])/', scandir('resources/conversations'));

            foreach ($files as $conversationName) {
                $this->importConversation($conversationName);
            }

            $this->info('Imports finished');
        } else {
            $this->info('OK, not running');
        }
    }

    protected function importConversation($conversationName): void
    {
        $this->info(sprintf('Importing conversation %s', $conversationName));

        $filename = base_path("resources/conversations/$conversationName");
        $model = file_get_contents($filename);

        $newConversation = Conversation::firstOrNew(['name' => $conversationName]);
        $newConversation->fill(['model' => $model]);
        $newConversation->save();

        $this->info(sprintf('Activating conversation with name %s', $newConversation->name));
        $newConversation->activateConversation();
    }
}
