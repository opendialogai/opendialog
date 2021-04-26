<?php

return [
    'items' =>
        [
            [
                [
                    'title' => 'Dashboard',
                    'url' => '/admin',
                    'icon' => 'home-2',
                ],
                [
                    'title' => 'Designer',
                    'url' => '/admin/conversation-builder/scenarios',
                    'icon' => 'filter-descending'
                ],
                [
                    'title' => 'Message Editor',
                    'url' => '/admin/outgoing-intents',
                    'icon' => 'message'
                ],
                [
                    'title' => 'Interface settings',
                    'url' => '/admin/webchat-setting',
                    'icon' => 'settings-sliders'
                ],
                [
                    'title' => 'Dynamic Attributes',
                    'url' => '/admin/dynamic-attributes',
                    'icon' => 'apps'
                ],
            ],
            [
                [
                    'title' => 'Chatbot Users',
                    'url' => '/admin/users',
                    'icon' => 'profile'
                ],
                [
                    'title' => 'Requests',
                    'url' => '/admin/requests',
                    'icon' => 'download'
                ],
                [
                    'title' => 'Warnings',
                    'url' => '/admin/warnings',
                    'icon' => 'notification-waiting'
                ]
            ],
            [
                [
                    'title' => 'Test Chatbot',
                    'url' => '/admin/demo',
                    'icon' => 'speech'
                ],
            ]
        ],
];
