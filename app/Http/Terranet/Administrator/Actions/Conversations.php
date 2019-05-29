<?php

namespace App\Http\Terranet\Administrator\Actions;

use App\Http\Terranet\Administrator\Actions\Handlers\PublishConversation;
use App\Http\Terranet\Administrator\Actions\Handlers\UnpublishConversation;
use Terranet\Administrator\Services\CrudActions;

class Conversations extends CrudActions
{
    public function actions()
    {
        return [
            PublishConversation::class,
            UnpublishConversation::class,
        ];
    }

    public function batchActions()
    {
        return array_merge(parent::batchActions(), [
            // CustomAction::class
        ]);
    }
}
