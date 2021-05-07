<?php


namespace App\Http\Requests;

use App\Rules\OdId;
use OpenDialogAi\Core\Conversation\ConversationObject;

trait ConversationObjectRequestTrait
{
    public function odIdRule(ConversationObject $parent = null): array
    {
        return [
            'od_id' => ['bail', 'string', 'filled', 'not_regex:/[\$\:\/]/', new OdId($parent)],
        ];
    }
}
