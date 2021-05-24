<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use OpenDialogAi\InterpreterEngine\Service\InterpreterComponentServiceInterface;

class ComponentRegistrationRule implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return resolve(InterpreterComponentServiceInterface::class)->has($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return ':input is not a registered component.';
    }
}
