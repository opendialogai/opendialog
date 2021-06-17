<?php

namespace App\Http\Requests;

use App\Rules\ComponentConfigurationRule;
use App\Rules\ComponentRegistrationRule;
use App\Rules\PublicUrlRule;
use App\Rules\UrlSchemeRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ComponentConfigurationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $rules = [
            'name' => [
                Rule::requiredIf($this->method() == 'POST'),
                'bail',
                'string',
                'filled',
                'unique:component_configurations,name'
            ],
            'component_id' => [
                'bail',
                Rule::requiredIf($this->method() == 'POST' || !is_null($this->get('configuration'))),
                'string',
                'filled',
                new ComponentRegistrationRule
            ],
            'configuration' => [
                'bail',
                Rule::requiredIf($this->method() == 'POST' || !is_null($this->get('component_id'))),
                'array',
                new ComponentConfigurationRule($this->get('component_id', ''))
            ],
            'active' => [
                'bail',
                'boolean',
            ]
        ];

        // Only set the URL validation rules if we are not in debug mode to allow local URLs during local development
        if (config('app.env') !== 'local') {
            $rules['configuration.app_url'] = [
                'active_url',
                new PublicUrlRule,
                new UrlSchemeRule
            ];

            $rules['configuration.webhook_url'] = [
                'active_url',
                new PublicUrlRule,
                new UrlSchemeRule
            ];
        }

        return $rules;
    }
}
