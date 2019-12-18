<?php

namespace App\Bot\Contexts;

use Illuminate\Support\Facades\Log;
use OpenDialogAi\ContextEngine\Contexts\Custom\AbstractCustomContext;
use OpenDialogAi\ContextEngine\Exceptions\AttributeIsNotSupported;
use OpenDialogAi\ContextEngine\Facades\AttributeResolver;
use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\Core\Attribute\StringAttribute;

/**
 * Generic context
 *
 */
class GlobalContext extends AbstractCustomContext
{
    public static $name = 'global';

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
     * @return AttributeInterface
     */
    private function createAttribute(\App\GlobalContext $context)
    {
        try {
            $attribute = AttributeResolver::getAttributeFor($context->name, $context->value);
        } catch (AttributeIsNotSupported $e) {
            Log::warning(
                sprintf(
                'Trying to create attribute %s from the global context, but it has not been bound to a type. Using a string',
                    $context->name
                )
            );

            $attribute = new StringAttribute($context->name, $context->value);
        }

        return $attribute;
    }
}
