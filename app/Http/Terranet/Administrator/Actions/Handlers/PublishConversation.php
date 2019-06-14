<?php

namespace App\Http\Terranet\Administrator\Actions\Handlers;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Terranet\Administrator\Traits\Actions\ActionSkeleton;
use Terranet\Administrator\Traits\Actions\Skeleton;

class PublishConversation
{
    use PublishConversationTrait;
    use Skeleton, ActionSkeleton;

    /**
     * Update single entity.
     *
     * @param Eloquent $conversation
     * @return mixed
     */
    public function handle(Eloquent $conversation)
    {
        return $this->publishConversation($conversation);
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
