<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use OpenDialogAi\ConversationBuilder\Conversation;
use OpenDialogAi\Core\Conversation\Conversation as ConversationNode;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;

class SetUpConversations extends Command
{
    protected $signature = 'conversations:setup {--non-interactive}';

    protected $description = 'Sets up some example conversations for the opendialog project';

    public function handle()
    {
        $conversations = config('opendialog.active_conversations');

        $continue =
            $this->option('non-interactive') ||
            $this->confirm(
                'This will clear your local dgraph and all conversations. Are you sure you want to continue?'
            );

        if ($continue) {
            $client = app()->make(DGraphClient::class);

            $this->info('Dropping Schema');
            $client->dropSchema();

            $this->info('Init Schema');
            $client->initSchema();

            $this->info('Setting all existing conversations to activatable');
            Conversation::all()->each(function (Conversation $conversation) {
                $conversation->status = ConversationNode::SAVED;
                $conversation->version_number = 0;
                $conversation->graph_uid = null;
                $conversation->save();
            });

            foreach ($conversations as $conversation) {
                $this->importConversation($conversation);
            }

            $this->info('Imports finished');
        } else {
            $this->info('OK, not running');
        }
    }

    protected function importConversation($conversationName): void
    {
        $this->info(sprintf('Importing conversation %s', $conversationName));
        Artisan::call(
            'conversation:import',
            [
                'filename' => "resources/conversations/$conversationName",
                '--activate' => true,
                '--yes' => true
            ]
        );
    }
}
