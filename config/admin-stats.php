<?php

use App\StatsContants;

return [

    'cards' => [
        [
            [
                'type' => StatsContants::LINE_CHART,
                'name' => 'Users',
                'endpoint' => '/stats/users',
                'width' => StatsContants::HALF,
            ],
            [
                'type' => StatsContants::LINE_CHART,
                'name' => 'Users',
                'endpoint' => '/stats/users',
                'width' => StatsContants::HALF,
            ],
        ],
        [
            [
                'type' => StatsContants::NUMBER,
                'name' => 'Cost',
                'endpoint' => '/stats/cost',
                'width' => StatsContants::THIRD,
            ],
            [
                'type' => StatsContants::NUMBER,
                'name' => 'Cost',
                'endpoint' => '/stats/cost',
                'width' => StatsContants::THIRD,
            ],
            [
                'type' => StatsContants::NUMBER,
                'name' => 'Cost',
                'endpoint' => '/stats/cost',
                'width' => StatsContants::THIRD,
            ],
        ],
    ],

];
