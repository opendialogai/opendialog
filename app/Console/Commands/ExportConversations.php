<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ExportConversations extends Command
{
    protected $signature = 'conversations:export';

    protected $description = 'Sets up some example conversations for the opendialog project';

    public function handle()
    {
        $conversations = config('opendialog.active_conversations');

        $continue = $this->confirm(
            sprintf(
                'Do you want to export the %d active conversations listed in opendialog.active_conversations config?',
                count($conversations)
            )
        );

        if ($continue) {
            foreach ($conversations as $conversation) {
                $this->exportConversation($conversation);
            }

            $this->info('Imports finished');
        } else {
            $this->info('Bye');
        }
    }

    protected function exportConversation($conversationName): void
    {
        $this->info(sprintf('Exporting conversation %s', $conversationName));
        Artisan::call(
            'conversation:export',
            [
                'conversation name' => $conversationName,
                '-f' => "resources/conversations/$conversationName",
            ]
        );
    }
}
