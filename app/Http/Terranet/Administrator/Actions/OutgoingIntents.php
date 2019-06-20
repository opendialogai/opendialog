<?php

namespace App\Http\Terranet\Administrator\Actions;

use App\Http\Terranet\Administrator\Actions\Handlers\CreateMessageTemplate;
use Terranet\Administrator\Services\CrudActions;

class OutgoingIntents extends CrudActions
{
    public function actions()
    {
        return [
            CreateMessageTemplate::class,
        ];
    }

    public function batchActions()
    {
        return array_merge(parent::batchActions(), [
            // CustomAction::class
        ]);
    }
}
