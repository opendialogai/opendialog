<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Status implements Rule
{
    public static array $validStatuses = ['DRAFT', 'PUBLISHED', 'LIVE'];

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return in_array($value, self::$validStatuses);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return sprintf(':value is not a valid :attribute. Must be one of %s', implode(',', self::$validStatuses));
    }
}
