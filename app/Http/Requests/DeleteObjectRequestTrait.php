<?php

namespace App\Http\Requests;

trait DeleteObjectRequestTrait
{
    public function authorize()
    {
        return true;
    }

    public function prepareRules(string $objectRuleClass)
    {
        return [
            'force' => ['boolean'],
            'transition_intents' => [new $objectRuleClass]
        ];
    }

    /**
     * Fetches the conversation ID from the route param
     */
    protected function prepareValidation(string $routeObjectName)
    {
        if (!$this->json('force')) {
            $this->merge(['transition_intents' => $this->route($routeObjectName)]);
        }
    }
}
