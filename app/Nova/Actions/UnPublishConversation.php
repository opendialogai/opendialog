<?php

namespace App\Nova\Actions;

use Ds\Map;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use OpenDialogAi\ConversationBuilder\ConversationStateLog;

class UnpublishConversation extends Action
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields $fields
     * @param  \Illuminate\Support\Collection $conversations
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $conversations)
    {
        foreach ($conversations as $conversation) {
            // Ensure that the conversation is published.
            if ($conversation->status !== 'published') {
                return Action::danger('Sorry, I can\'t unpublish a conversation that\'s not published!');
            }

            // Unpublish the conversation.
            if (!$conversation->unPublishConversation()) {
                $this->logMessage($conversation->id, 'Unable to unpublish conversation from DGraph.');
                return Action::danger('Sorry, I wasn\'t able to unpublish this conversation from DGraph!');
            }

            return Action::message('Conversation unpublished.');
        }
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [];
    }

    /**
     * Create a Conversation State Log message.
     */
    private function logMessage($conversationId, $message)
    {
        $log = new ConversationStateLog();
        $log->conversation_id = $conversationId;
        $log->message = $message;
        $log->type = 'unpublish_conversation';
        $log->save();
    }
}
