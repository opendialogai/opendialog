<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use OpenDialogAi\ConversationBuilder\Conversation;

class SetUpConversations extends Command
{
    protected $signature = 'conversations:setup {--y|yes} {--activate|activate}';

    protected $description = 'Sets up all active conversations';

    public function handle()
    {
        if ($this->option('yes')) {
            $continue = true;
        } else {
            $continue = $this->confirm(
                'This will import or update all active conversations. Are you sure you want to continue?'
            );
        }

        if ($continue) {
            $activate = ($this->option('activate')) ? true : false;

            $files = preg_grep('/^([^.])/', scandir(base_path('resources/conversations')));

            foreach ($files as $conversationFileName) {
                $this->importConversation($conversationFileName, $activate);
            }

            $this->info('Imports finished');
        } else {
            $this->info('OK, not running');
        }
    }

    protected function importConversation($conversationFileName, $activate): void
    {
        $conversationName = preg_replace('/.conv$/', '', $conversationFileName);

        $this->info(sprintf('Importing conversation %s', $conversationName));

        $filename = base_path("resources/conversations/$conversationFileName");
        $model = file_get_contents($filename);

        $newConversation = Conversation::firstOrNew(['name' => $conversationName]);
        $newConversation->fill(['model' => $model]);
        $newConversation->save();

        if ($activate) {
            $this->info(sprintf('Activating conversation with name %s', $newConversation->name));
            $newConversation->activateConversation();
        }
    }
}
