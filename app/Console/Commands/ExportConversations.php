<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use OpenDialogAi\ConversationBuilder\Conversation;

class ExportConversations extends Command
{
    protected $signature = 'conversations:export';

    protected $description = 'Export all conversations';

    public function handle()
    {
        $conversations = config('opendialog.active_conversations');

        $continue = $this->confirm('Do you want to export all conversations?');

        if ($continue) {
            $conversations = Conversation::all();

            foreach ($conversations as $conversation) {
                $this->exportConversation($conversation);
            }

            $this->info('Exports finished');
        } else {
            $this->info('Bye');
        }
    }

    protected function exportConversation(Conversation $conversation): void
    {
        $this->info(sprintf('Exporting conversation %s', $conversation->name));

        $filename = "resources/conversations/$conversation->name";
        file_put_contents($filename, $conversation->model);
    }
}
