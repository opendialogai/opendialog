<?php

namespace App\Http\Terranet\Administrator\Actions\Handlers;

use Ds\Map;
use Illuminate\Database\Eloquent\Model as Eloquent;
use OpenDialogAi\ConversationBuilder\ConversationStateLog;
use Terranet\Administrator\Traits\Actions\ActionSkeleton;
use Terranet\Administrator\Traits\Actions\Skeleton;

class PublishConversation
{
    use Skeleton, ActionSkeleton;

    /**
     * Update single entity.
     *
     * @param Eloquent $conversation
     * @return mixed
     */
    public function handle(Eloquent $conversation)
    {
				// Ensure that the conversation has not already been published.
				if ($conversation->status === 'published') {
            return back()->with(
                'messages',
                'Sorry, I can\'t publish a conversation that\'s already been published!'
            );
				}

				// Ensure that the conversation has been validated.
				if ($conversation->status !== 'validated') {
            return back()->with(
                'messages',
                'Sorry, I can\'t publish a conversation until it has been validated!'
            );
				}

				// Get the conversation's representation.
				$model = $conversation->buildConversation();
				if (!$model->getAllScenes() instanceof Map) {
						$this->logMessage($conversation->id, 'Unable to build conversation model.');
            return back()->with(
                'messages',
                'Sorry, I wasn\'t able to prepare this conversation to be published!'
            );
				}

				// Publish the conversation.
				if (!$conversation->publishConversation($model)) {
						$this->logMessage($conversation->id, 'Unable to publish conversation to DGraph.');
            return back()->with(
                'messages',
                'Sorry, I wasn\'t able to publish this conversation to DGraph!'
            );
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
