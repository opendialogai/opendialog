<?php

namespace App\Http\Terranet\Administrator\Actions\Handlers;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Terranet\Administrator\Traits\Actions\ActionSkeleton;
use Terranet\Administrator\Traits\Actions\Skeleton;

class UnpublishConversation
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
				// Ensure that the conversation is published.
				if ($conversation->status !== 'published') {
            return back()->with(
                'messages',
                'Sorry, I can\'t unpublish a conversation that\'s not published!'
            );
				}

				// Unpublish the conversation.
				if (!$conversation->unPublishConversation()) {
						$this->logMessage($conversation->id, 'Unable to unpublish conversation from DGraph.');
            return back()->with(
                'messages',
                'Sorry, I wasn\'t able to unpublish this conversation from DGraph!'
            );
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

    /**
     * @param Eloquent $entity
     *
     * @return string
     */
    protected function icon(Eloquent $entity = null)
    {
        return 'fa-ban';
    }
}
