<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class UrlSchemeRule implements Rule
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
        $result = parse_url($value);
        $scheme = isset($result['scheme']) ? strtolower($result['scheme']) : null;

        return in_array($scheme, ['http', 'https']);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The provided URL is not http or https scheme.';
    }
}
