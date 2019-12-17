<?php

namespace App\Bot\Contexts;

use App\GlobalContext;
use OpenDialogAi\ContextEngine\Contexts\Custom\AbstractCustomContext;
use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\Core\Attribute\ArrayAttribute;
use OpenDialogAi\Core\Attribute\BooleanAttribute;
use OpenDialogAi\Core\Attribute\FloatAttribute;
use OpenDialogAi\Core\Attribute\IntAttribute;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Attribute\TimestampAttribute;

/**
 * Generic context
 *
 */
class GenericContext extends AbstractCustomContext
{
    public static $name = 'generic';

    /**
     * A function to load all custom attributes from any external sources into this custom context.
     *
     * All attributes should be added using @see AbstractContext::addAttribute()
     */
    public function loadAttributes(): void
    {
        GlobalContext::all()->each(function (GlobalContext $context) {
            $this->addAttribute($this->createAttribute($context));
        });
    }

    /**
     * @param GlobalContext $context
     * @return AttributeInterface
     */
    private function createAttribute(GlobalContext $context)
    {
        $attribute = false;

        switch ($context->type) {
            case 'array':
                $attribute = new ArrayAttribute($context->name, $context->value);
                break;
            case 'boolean':
                $attribute = new BooleanAttribute($context->name, $context->value);
                break;
            case 'float':
                $attribute = new FloatAttribute($context->name, $context->value);
                break;
            case 'int':
                $attribute = new IntAttribute($context->name, $context->value);
                break;
            case 'string':
                $attribute = new StringAttribute($context->name, $context->value);
                break;
            case 'timestamp':
                $attribute = new TimestampAttribute($context->name, $context->value);
                break;
        }

        return $attribute;
    }
}
