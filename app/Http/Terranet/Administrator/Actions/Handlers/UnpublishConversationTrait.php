<?php

namespace App\Http\Terranet\Administrator\Actions\Handlers;

use OpenDialogAi\ConversationBuilder\Conversation;
use OpenDialogAi\ConversationBuilder\ConversationStateLog;

trait UnpublishConversationTrait
{
    public function unpublishConversation(Conversation $conversation)
    {
        // Ensure that the conversation is published.
        if ($conversation->status !== 'published') {
            return back()->withErrors([
                'Sorry, I can\'t unpublish a conversation that\'s not published!'
            ]);
        }

        // Unpublish the conversation.
        if (!$conversation->unPublishConversation()) {
            $this->logMessage($conversation->id, 'Unable to unpublish conversation from DGraph.');
            return back()->withErrors([
                'Sorry, I wasn\'t able to unpublish this conversation from DGraph!'
            ]);
        }

        return back()->with(
            'messages',
            'Conversation unpublished.'
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
        $log->type = 'unpublish_conversation';
        $log->save();
    }
}
