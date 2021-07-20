<?php

namespace App\Rules;

use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Facades\IntentDataClient;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\IntentCollection;

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
        $this->linkedIntents = self::getIntentsThatTransitionTo($value->getUid());

        return $this->linkedIntents->count() === 0;
    }

    /**
     * @param string $conversationUid
     * @return IntentCollection
     */
    public static function getIntentsThatTransitionTo(string $conversationUid): IntentCollection
    {
        $linkedIntents = IntentDataClient::getIntentWithConversationTransition($conversationUid);

        return $linkedIntents->filter(function (Intent $intent) use ($conversationUid) {
            return $intent->getTurn()->getScene()->getConversation()->getUid() !== $conversationUid;
        });
    }
}
