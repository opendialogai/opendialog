<?php

use App\StatsContants;

return [

    'cards' => [
        [
            [
                'type' => StatsContants::LINE_CHART,
                'name' => 'Chatbot Users',
                'endpoint' => '/stats/chatbot-users',
                'width' => StatsContants::HALF,
            ],
            [
                'type' => StatsContants::LINE_CHART,
                'name' => 'Requests',
                'endpoint' => '/stats/requests',
                'width' => StatsContants::HALF,
            ],
        ],
        [
            [
                'type' => StatsContants::NUMBER,
                'name' => 'Active Scenarios',
                'endpoint' => '/stats/scenarios',
                'width' => StatsContants::THIRD,
            ],
            [
                'type' => StatsContants::NUMBER,
                'name' => 'Active Conversations',
                'endpoint' => '/stats/conversations',
                'width' => StatsContants::THIRD,
            ],
            [
                'type' => StatsContants::NUMBER,
                'name' => 'Outgoing Intents',
                'endpoint' => '/stats/message-templates',
                'width' => StatsContants::THIRD,
            ],
        ],
    ],

    'cache_length' => env('STATS_CACHE_LENGTH', 21600),

];
