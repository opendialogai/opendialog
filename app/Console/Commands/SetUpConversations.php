<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use OpenDialogAi\ConversationBuilder\Conversation;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;

class SetUpConversations extends Command
{
    protected $signature = 'conversations:setup';

    protected $description = 'Sets up some example conversations for the opendialog project';

    public function handle()
    {
        $conversations = config('opendialog.active_conversations');

        if (!$this->confirm('This will clear your local dgraph and all conversations. ' .
            'Are you sure you want to continue?')) {
            $this->info("OK, not running");
            exit;
        }

        $client = app()->make(DGraphClient::class);

        $this->info('Dropping Schema');
        $client->dropSchema();

        $this->info('Init Schema');
        $client->initSchema();

        $this->info('Setting all existing conversations to unpublished');
        Conversation::all()->each(function (Conversation $conversation) {
            $conversation->status = 'validated';
            $conversation->save();
        });

        foreach ($conversations as $conversation) {
            $this->importConversation($conversation);
        }

        $this->info('Imports finished');
    }

    protected function importConversation($conversationName): void
    {
        $this->info(sprintf('Importing conversation %s', $conversationName));
        Artisan::call(
            'conversation:import',
            [
                'filename' => "resources/conversations/$conversationName",
                '--publish' => true,
                '--yes' => true
            ]
        );
    }
}
