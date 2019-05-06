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

class PublishConversation extends Action
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
            // Ensure that the conversation has not already been published.
            if ($conversation->status === 'published') {
                return Action::danger('Sorry, I can\'t publish a conversation that\'s already been published!');
            }

            // Ensure that the conversation has been validated.
            if ($conversation->status !== 'validated') {
                return Action::danger('Sorry, I can\'t publish a conversation until it has been validated!');
            }

            // Get the conversation's representation.
            $model = $conversation->buildConversation();
            if (!$model->getAllScenes() instanceof Map) {
                $this->logMessage($conversation->id, 'Unable to build conversation model.');
                return Action::danger('Sorry, I wasn\'t able to prepare this conversation to be published!');
            }

            // Publish the conversation.
            if (!$conversation->publishConversation($model)) {
                $this->logMessage($conversation->id, 'Unable to publish conversation to DGraph.');
                return Action::danger('Sorry, I wasn\'t able to publish this conversation to DGraph!');
            }

            return Action::message('Conversation published.');
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
        $log->type = 'publish_conversation';
        $log->save();
    }
}
