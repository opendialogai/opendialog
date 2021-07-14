<?php

return [
    /*
     * Settings for Google Tag Manager. These are use to optionally add google tag manager to the admin pages
     */
    'enable' => env("ENABLE_GTM", false),
    'auth' => env("GTM_AUTH"),
    'environment' => env('GTM_ENVIRONMENT'),
    'container' => env("GTM_CONTAINER"),

];
