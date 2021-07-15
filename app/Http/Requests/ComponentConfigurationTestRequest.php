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
        $rules['configuration.app_url'] = $originalRules['configuration.app_url'] ?? [];
        $rules['configuration.webhook_url'] = $originalRules['configuration.webhook_url'] ?? [];

        return $rules;
    }
}
