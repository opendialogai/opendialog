<?php

return [
    // URL Prefix
    'prefix' => $prefix = 'cms',

    'title' => '<b>Admin</b> Architect',
    'abbreviation' => 'AA',
    'welcome' => 'Welcome! Please sign In.',

    // Authentication options
    'auth' => [
        'identity' => 'email',
        'credential' => 'password',
        'model' => \App\User::class,
        'conditions' => null,
    ],

    'paths' => [
        // media storage
        'media' => 'media',

        'module' => 'Http/Terranet/Administrator/Modules',
        'action' => 'Http/Terranet/Administrator/Actions',
        'action_handler' => 'Http/Terranet/Administrator/Actions/Handlers',
        'panel' => 'Http/Terranet/Administrator/Dashboard',
        'finder' => 'Http/Terranet/Administrator/Finders',
        'saver' => 'Http/Terranet/Administrator/Savers',
        'column' => 'Http/Terranet/Administrator/Decorators',
        'template' => 'Http/Terranet/Administrator/Templates',
        'widget' => 'Http/Terranet/Administrator/Widgets',
        'badge' => 'Http/Terranet/Administrator/Badges',
        'breadcrumbs' => 'Http/Terranet/Administrator/Breadcrumbs',
    ],

    // Handle passwords -> Convert plain text to Hash
    'manage_passwords' => true,

    // Enable File Manager
    'file_manager' => false,

    'gravatar' => false,

    // The menu item that should be used as the default landing page of the administrative section
    'home_page' => $prefix,

    // Basic user validation Rule
    'permission' => \Terranet\Administrator\Auth\SuperAdminRule::class,

    // Navigation Factory
    'menu' => \Terranet\Administrator\Navigation\Factory::class,

    // Dashboard Panels Factory
    'dashboard' => \App\Http\Terranet\Administrator\Dashboard\Factory::class,

    'resource' => [
        // The custom way to resolve module name for custom resources
        // when controller missing Router's $module parameter
        'resolver' => null,

        // Default segment for module name resolver
        // /admin/pages - admin => 1, pages => 2
        'segment' => 2,
    ],

    'acl' => [
        // Global ACL Manager, which used to control CRUD actions
        // in the global way. Can provide optional methods:
        // showIf, can(Index|Update|Delete|Create)
        'manager' => null,
    ],

    // main layouts
    'layouts' => [
        'app' => 'administrator::layouts.app',
        'popup' => 'administrator::layouts.popup',
        'dashboard' => 'administrator::layouts.dashboard',
        'settings' => 'administrator::layouts.settings',
        'exportable' => 'administrator::layouts.exportable',
    ],

    'grid' => [
        'timestamps' => [
            'enabled' => false,
            //'users' => true,
        ],
    ],

    // Exportable formats declaration
    'export' => [
        'default' => ['xml', 'csv', 'json'],
        // 'users' => ['csv', 'pdf'],
    ],

    'translations' => [
        'enabled' => false,
        'filters' => [
            // build filters based on specific translation files
            'only' => null, // ['auth', 'validation']
            // except some translation files from filters
            'except' => null, // ['password', 'pagination']
        ],
    ],
];
