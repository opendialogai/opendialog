<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use OpenDialogAi\Core\Conversation\Facades\IntentDataClient;
use OpenDialogAi\Core\Conversation\IntentCollection;

class SceneInTransition implements Rule
{
    private IntentCollection $linkedIntents;

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $this->linkedIntents = IntentDataClient::getIntentWithSceneTransition($value);
        return $this->linkedIntents->count() === 0;
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
