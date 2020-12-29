<?php

namespace App\Console\Commands\Specification;

use App\ImportExportHelpers\BaseImportExportHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use OpenDialogAi\ConversationBuilder\Conversation;
use OpenDialogAi\Core\Conversation\Conversation as ConversationNode;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;

class SetupConversations extends Command
{
    protected $signature = 'conversations:setup';

    protected $description = 'Sets up some example conversations for the OpenDialog project';

    public function handle()
    {
        if (count(BaseImportExportHelper::getDisk()->allFiles()) > 0) {
            $this->error("Your conversation data is now in the new format and location, this command will not import it."
                . " Please use the new `php artisan specification:import` command instead.");
            return;
        } else {
            $this->warn("This command is deprecated, along with the way conversation data is stored. After this"
                . " command has run please then run `php artisan specification:export` to export your conversation"
                . " data in the new location, which will now include separate intent and message files.");
        }

        $conversations = config('opendialog.active_conversations');

        $continue = $this->confirm(
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
