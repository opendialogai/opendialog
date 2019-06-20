<?php

namespace App\Http\Terranet\Administrator\Actions\Handlers;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Terranet\Administrator\Traits\Actions\ActionSkeleton;
use Terranet\Administrator\Traits\Actions\Skeleton;

class CreateMessageTemplate
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
    }

    /**
     * @param Eloquent $entity
     *
     * @return string
     */
    protected function route(Eloquent $entity = null)
    {
        return route('scaffold.create', [
            'module' => 'message_templates',
            'outgoing_intent' => $entity->id,
        ]);
    }

    /**
     * @param Eloquent $entity
     *
     * @return string
     */
    protected function icon(Eloquent $entity = null)
    {
        return 'fa-plus';
    }
}
