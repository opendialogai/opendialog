<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use OpenDialogAi\ActionEngine\Service\ActionComponentServiceInterface;
use OpenDialogAi\Core\Components\Exceptions\UnknownComponentTypeException;
use OpenDialogAi\Core\Components\Helper\ComponentHelper;
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
        try {
            $type = ComponentHelper::parseComponentId($value);
        } catch (UnknownComponentTypeException $e) {
            return false;
        }

        switch ($type) {
            case ComponentHelper::INTERPRETER:
                return resolve(InterpreterComponentServiceInterface::class)->has($value);
            case ComponentHelper::ACTION:
                return resolve(ActionComponentServiceInterface::class)->has($value);
        }
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
