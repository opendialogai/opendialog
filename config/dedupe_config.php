<?php

return [
    'dedupe_timeout' => env('LOG_DEDUPE_TIMEOUT', 60),
    'dedupe_error_level' => env('LOG_DEDUPE_ERROR_LEVEL', \Monolog\Logger::ERROR),
    'dedupe_file_location' => env('LOG_DEDUPE_FILE', storage_path('logs/dedupe.log')),
    'dedupe_slack_webchook' => env('LOG_SLACK_WEBHOOK_URL')
];
