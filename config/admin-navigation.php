<?php

return [

    'items' => [
        [
            'title' => 'Message Editor',
            'url' => '/admin/outgoing-intents',
            'icon' => 'icon-list',
        ],
        [
            'title' => 'Chatbot Users',
            'url' => '/admin/chatbot-users',
            'icon' => 'icon-layers',
        ],
        [
            'title' => 'Users',
            'url' => '/admin/users',
            'icon' => 'icon-people',
        ],
        [
            'title' => 'Webchat settings',
            'url' => '/admin/webchat-setting',
            'icon' => 'icon-settings',
            'children' => '/admin/api/webchat-settings-categories',
        ],
        [
            'title' => 'Conversations',
            'url' => '/admin/conversations',
            'icon' => 'icon-speech',
            'children' => '/admin/api/conversations-list',
        ],
        [
            'title' => 'Requests',
            'url' => '/admin/requests',
            'icon' => 'cui-inbox',
        ],
        [
            'title' => 'Test Bot',
            'url' => '/admin/demo',
            'icon' => 'icon-control-play',
        ],
    ],

];
