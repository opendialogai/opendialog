<?php

namespace App\Http\Terranet\Administrator\Actions\Handlers;

use Ds\Map;
use OpenDialogAi\ConversationBuilder\Conversation;
use OpenDialogAi\ConversationBuilder\ConversationStateLog;

trait PublishConversationTrait
{
    public function publishConversation(Conversation $conversation)
    {
        // Ensure that the conversation has not already been published.
        if ($conversation->status === 'published') {
            return back()->withErrors([
                'Sorry, I can\'t publish a conversation that\'s already been published!'
            ]);
        }

        // Ensure that the conversation has been validated.
        if ($conversation->status !== 'validated') {
            return back()->withErrors([
                'Sorry, I can\'t publish a conversation until it has been validated!'
            ]);
        }

        // Get the conversation's representation.
        $model = $conversation->buildConversation();
        if (!$model->getAllScenes() instanceof Map) {
            $this->logMessage($conversation->id, 'Unable to build conversation model.');
            return back()->withErrors([
                'Sorry, I wasn\'t able to prepare this conversation to be published!'
            ]);
        }

        // Publish the conversation.
        if (!$conversation->publishConversation($model)) {
            $this->logMessage($conversation->id, 'Unable to publish conversation to DGraph.');
            return back()->withErrors([
                'Sorry, I wasn\'t able to publish this conversation to DGraph!'
            ]);
        }

        return back()->with(
            'messages',
            'Conversation published.'
        );
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
