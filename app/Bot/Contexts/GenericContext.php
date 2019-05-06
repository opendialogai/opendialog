<?php

namespace App\Bot\Contexts;

use App\GenericContent;
use OpenDialogAi\ContextEngine\Contexts\Custom\AbstractCustomContext;
use OpenDialogAi\Core\Attribute\StringAttribute;

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
        GenericContent::all()->each(function (GenericContent $content) {
            $attribute = new StringAttribute($content->name, $content->value);
            $this->addAttribute($attribute);
        });
    }
}
