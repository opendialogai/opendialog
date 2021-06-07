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
                    'url' => '/admin/message-editor',
                    'icon' => 'edit-bubble'
                ],
                [
                    'title' => 'Interpreters Setup',
                    'url' => '/admin/interpreters',
                    'icon' => 'pattern'
                ],
                [
                    'title' => 'Interface settings',
                    'url' => '/admin/webchat-setting',
                    'icon' => 'settings-sliders'
                ],
            ],
            [
                [
                    'title' => 'Preview',
                    'url' => '/admin/demo',
                    'icon' => 'speech'
                ],
            ]
        ],
    'help' => [
        [
            'title' => 'Documentation',
            'url' => 'https://docs.opendialog.ai',
            'icon' => 'document'
        ],
        [
            'title' => 'Contact Us',
            'url' => 'https://opendialog.ai/contact-us',
            'icon' => 'email'
        ]
    ]
];
