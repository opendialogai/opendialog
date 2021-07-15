<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\IntentCollection;

abstract class BaseTransitionRule implements Rule
{
    protected $linkedIntents;

    public function message()
    {
        return $this->getErrorMessage($this->linkedIntents);
    }

    protected function getErrorMessage(IntentCollection $linkedIntents): array
    {
        $affectedIntents = [];

        $linkedIntents->each(function (Intent $intent) use (&$affectedIntents) {
            $affectedIntents[] = [$intent->getName() => $intent->getUid()];
        });

        return $affectedIntents;
    }
}
