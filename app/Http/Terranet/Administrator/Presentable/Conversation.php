<?php

namespace App\Http\Terranet\Administrator\Presentable;

use App\Http\Terranet\Administrator\Presentable\ConversationPresenter;
use OpenDialogAi\ConversationBuilder\Conversation as OdConversation;
use Terranet\Presentable\PresentableInterface;
use Terranet\Presentable\PresentableTrait;

class Conversation extends OdConversation implements PresentableInterface
{
    use PresentableTrait;

    protected $fillable = [
        'id',
        'name',
        'model',
        'notes',
        'status',
        'yaml_validation_status',
        'yaml_schema_validation_status',
        'scenes_validation_status',
        'model_validation_status',
        'opening_intent',
        'outgoing_intents',
    ];

    protected $presenter = ConversationPresenter::class;
}
