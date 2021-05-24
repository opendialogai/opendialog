<?php

namespace App\Http\Requests;

class ComponentConfigurationTestRequest extends ComponentConfigurationRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $originalRules = parent::rules();

        $rules = [];
        $rules['component_id'] = $originalRules['component_id'];
        $rules['configuration'] = $originalRules['configuration'];

        return $rules;
    }
}
