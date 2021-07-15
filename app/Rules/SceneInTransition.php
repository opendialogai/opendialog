<?php

namespace App\Rules;

use OpenDialogAi\Core\Conversation\Facades\IntentDataClient;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Scene;

class SceneInTransition extends BaseTransitionRule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  Scene  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $linkedIntents = IntentDataClient::getIntentWithSceneTransition($value->getUid());

        $linkedIntents = $linkedIntents->filter(function (Intent $intent) use ($value) {
            return $intent->getTurn()->getScene()->getUid() !== $value->getUid();
        });

        $this->linkedIntents = $linkedIntents;
        return $linkedIntents->count() === 0;
    }
}
