<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use OpenDialogAi\AttributeEngine\Facades\AttributeResolver;
use OpenDialogAi\ContextEngine\Facades\ContextService;

class UserContextController extends Controller
{
    private static array $ignoreList = ['utterance', 'utterance_user', 'custom'];

    public function getUserContext($userId)
    {
        ContextService::setupPersistentContexts($userId);

        try {
            $userContext = ContextService::getContext('user');
            $attributes = $userContext->getAttributes();
            $userAttributes = $this->formatAttributes($attributes);

            $globalContext = ContextService::getContext('global');
            $globalContext->loadAttributes();
            $globalAttributes = $this->formatAttributes($globalContext->getAttributes());

            return [
                'user' => $userAttributes,
                'global' => $globalAttributes
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function addToUserContext($userId, Request $request)
    {
        $key = $request->get('key');
        $value = $request->get('value');

        ContextService::setupPersistentContexts($userId);

        $context = ContextService::getContext('user');
        $context->addAttribute(AttributeResolver::getAttributeFor($key, $value));
        $context->persist();

        return 'ok';
    }

    private function formatAttributes(\Ds\Map $attributes): array
    {
        $formatted = [];
        foreach ($attributes as $key => $values) {
            if (!in_array($key, self::$ignoreList)) {
                $formatted[$key] = $values->getAttributeValue()->getRawValue();
            }
        }

        return $formatted;
    }
}
