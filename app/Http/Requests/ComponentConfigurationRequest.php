<?php

namespace App\Http\Requests;

use App\Rules\ComponentConfigurationRule;
use App\Rules\ComponentRegistrationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ComponentConfigurationRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

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
        return [
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
    }
}
