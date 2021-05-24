<?php

namespace App\Bot\Contexts;

use OpenDialogAi\AttributeEngine\Contracts\Attribute;
use OpenDialogAi\AttributeEngine\Facades\AttributeResolver;
use OpenDialogAi\ContextEngine\Contexts\Custom\AbstractCustomContext;

/**
 * Generic context
 *
 */
class GlobalContext extends AbstractCustomContext
{
    public static string $componentId = 'global';
    public static ?string $componentName = 'Global';

    /**
     * A function to load all custom attributes from any external sources into this custom context.
     *
     * All attributes should be added using @see AbstractContext::addAttribute()
     */
    public function loadAttributes(): void
    {
        \App\GlobalContext::all()->each(function (\App\GlobalContext $context) {
            $this->addAttribute($this->createAttribute($context));
        });
    }

    /**
     * @param \App\GlobalContext $context
     * @return Attribute
     */
    private function createAttribute(\App\GlobalContext $context)
    {
        return AttributeResolver::getAttributeFor($context->name, $context->value);
    }
}
