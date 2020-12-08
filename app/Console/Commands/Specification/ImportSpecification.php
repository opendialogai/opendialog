<?php

namespace App\Console\Commands\Specification;

use OpenDialogAi\ConversationBuilder\Conversation;
use OpenDialogAi\Core\Conversation\Conversation as ConversationNode;
use OpenDialogAi\ResponseEngine\OutgoingIntent;

class ImportSpecification extends BaseSpecificationCommand
{
    protected $signature = 'specification:import {--y|yes}';

    protected $description = '';

    public function handle()
    {
        if ($this->option('yes')) {
            $continue = true;
        } else {
            $continue = $this->confirm('Do you want to remove all conversations, intents, messages and import the new ones?');
        }

        if ($continue) {
            $outgoingIntents = OutgoingIntent::all();
            $conversations = Conversation::all();

            foreach ($outgoingIntents as $outgoingIntent) {
                $outgoingIntent->delete();

                $this->info(sprintf('Deleted outgoing intent %s', $outgoingIntent->name));
            }

            foreach ($conversations as $conversation) {
                if ($conversation->status == ConversationNode::ACTIVATED) {
                    $conversation->deactivateConversation();
                    $conversation->archiveConversation();
                } elseif ($conversation->status == ConversationNode::DEACTIVATED) {
                    $conversation->archiveConversation();
                }

                $conversation->delete();

                $this->info(sprintf('Deleted conversation %s', $conversation->name));
            }

            $this->call(
                'conversations:import',
                [
                    '--yes' => true
                ]
            );

            $this->call(
                'intents:import',
                [
                    '--yes' => true
                ]
            );

            $this->call(
                'messages:import',
                [
                    '--yes' => true
                ]
            );
        }
    }
}
