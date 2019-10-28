<?php

namespace App\Logging;

use Illuminate\Support\Facades\Log;
use Monolog\Handler\DeduplicationHandler;
use Monolog\Handler\SlackWebhookHandler;
use Monolog\Logger;

class CreateDedupeLogger
{
    /**
     * Create a custom Monolog instance.
     *
     * @param array $config
     * @return \Monolog\Logger
     */
    public function __invoke(array $config)
    {
        $slackHandler = new SlackWebhookHandler(
            config('dedupe_config.dedupe_slack_webchook'),
            null,
            'Laravel Log',
            true,
            ':boom:',
            true,
            true,
            config('dedupe_config.dedupe_error_level'),
            true
        );

        $deduper = new DeduplicationHandler(
            $slackHandler,
            storage_path('logs/dedupe.log'),
            config('dedupe_config.dedupe_error_level'),
            config('dedupe_config.dedupe_timeout'),
            true
        );

        return new Logger('deduper', [$deduper]);
    }
}
