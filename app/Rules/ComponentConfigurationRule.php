<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use OpenDialogAi\Core\InterpreterEngine\Exceptions\InvalidConfigurationDataException;
use OpenDialogAi\InterpreterEngine\Facades\InterpreterService;

class ComponentConfigurationRule implements Rule
{
    private string $componentId;
    private string $errorMessage;

    public function __construct(string $componentId)
    {
        $this->componentId = $componentId;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $interpreter = InterpreterService::getInterpreter($this->componentId);

        try {
            $interpreter::createConfiguration('Component', $value);
        } catch (InvalidConfigurationDataException $e) {
            $this->errorMessage = $e->getMessage();
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return sprintf('The provided configuration was not valid for %s. %s', $this->componentId, $this->errorMessage);
    }
}
