<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class PublicUrlRule implements Rule
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
        $path = isset($result['host']) ? strtolower($result['host']) : $result['path'];

        $ip = gethostbyname($path);
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE |  FILTER_FLAG_NO_RES_RANGE);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The URL used is not valid or points to a private IP.';
    }
}
