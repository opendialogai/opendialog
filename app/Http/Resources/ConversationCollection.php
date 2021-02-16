<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use OpenDialogAi\ConversationBuilder\Conversation;

class ConversationCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->collection->transform(function (ConversationResource $c) {
            return new ConversationBuilderResource($c->resource);
        });
        return parent::toArray($request);
    }
}
