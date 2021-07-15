<?php

namespace App\Rules;

use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Facades\IntentDataClient;
use OpenDialogAi\Core\Conversation\Intent;

class ConversationInTransition extends BaseTransitionRule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  Conversation  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $linkedIntents = IntentDataClient::getIntentWithConversationTransition($value->getUid());

        $linkedIntents = $linkedIntents->filter(function (Intent $intent) use ($value) {
            return $intent->getTurn()->getScene()->getConversation()->getUid() !== $value->getUid();
        });

        $this->linkedIntents = $linkedIntents;

        return $linkedIntents->count() === 0;
    }
}
