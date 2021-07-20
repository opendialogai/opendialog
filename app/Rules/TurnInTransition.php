<?php

namespace App\Rules;

use OpenDialogAi\Core\Conversation\Facades\IntentDataClient;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\IntentCollection;
use OpenDialogAi\Core\Conversation\Turn;

class TurnInTransition extends BaseTransitionRule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  Turn  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $this->linkedIntents = self::getIntentsThatTransitionTo($value->getUid());

        return $this->linkedIntents->count() === 0;
    }

    /**
     * @param string $turnUid
     * @return IntentCollection
     */
    public static function getIntentsThatTransitionTo(string $turnUid): IntentCollection
    {
        $linkedIntents = IntentDataClient::getIntentWithTurnTransition($turnUid);

        return $linkedIntents->filter(function (Intent $intent) use ($turnUid) {
            return $intent->getTurn()->getUid() !== $turnUid;
        });
    }
}
