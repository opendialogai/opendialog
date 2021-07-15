<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use OpenDialogAi\Core\Conversation\Facades\IntentDataClient;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\IntentCollection;
use OpenDialogAi\Core\Conversation\Scene;

class SceneInTransition implements Rule
{
    private IntentCollection $linkedIntents;

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

        return $linkedIntents->count() === 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The scene is used in a transition.';
    }
}
