<?php

namespace App\Rules;

use OpenDialogAi\Core\Conversation\Facades\IntentDataClient;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\IntentCollection;
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
        $this->linkedIntents = self::getIntentsThatTransitionTo($value->getUid());

        return $this->linkedIntents->count() === 0;
    }

    /**
     * @param string $sceneUid
     * @return IntentCollection
     */
    public static function getIntentsThatTransitionTo(string $sceneUid): IntentCollection
    {
        $linkedIntents = IntentDataClient::getIntentWithSceneTransition($sceneUid);

        return $linkedIntents->filter(function (Intent $intent) use ($sceneUid) {
            return $intent->getTurn()->getScene()->getUid() !== $sceneUid;
        });
    }
}
