<?php

return [
    /**
     * Register your custom contexts here. Custom contexts must extend
     * @see \OpenDialogAi\ContextEngine\Contexts\Custom\AbstractCustomContext
     *
     * Custom contexts are used to make available application specific attributes that are externally managed
     */
    'custom_contexts' => [
//        \OpenDialogAi\ContextEngine\tests\contexts\DummyCustomContext::class
        \App\Bot\Contexts\GlobalContext::class,
    ]
];
