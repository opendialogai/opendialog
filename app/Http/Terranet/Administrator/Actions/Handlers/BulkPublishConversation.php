<?php

namespace App\Http\Terranet\Administrator\Actions\Handlers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model as Eloquent;
use OpenDialogAi\ConversationBuilder\Conversation;
use Terranet\Administrator\Traits\Actions\BatchSkeleton;
use Terranet\Administrator\Traits\Actions\Skeleton;

class BulkPublishConversation
{
    use PublishConversationTrait;
    use Skeleton, BatchSkeleton;

    /**
     * Perform a batch action.
     *
     * @param Eloquent $entity
     * @param Request $request
     * @return mixed
     */
    public function handle(Eloquent $entity, Request $request = null)
    {
        $collection = $request->get('collection');

        $conversations = Conversation::find($collection);

        foreach ($conversations as $conversation) {
            $this->publishConversation($conversation);
        }

        return $entity;
    }

    /**
     * @param Eloquent $entity
     *
     * @return string
     */
    protected function icon(Eloquent $entity = null)
    {
        return 'fa-cloud';
    }
}
