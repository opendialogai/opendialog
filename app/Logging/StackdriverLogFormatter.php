<?php

namespace App\Logging;

use Monolog\Formatter\NormalizerFormatter;

class StackdriverLogFormatter extends NormalizerFormatter
{
    public function __construct()
    {
        parent::__construct();
    }

    public function format(array $record)
    {
        $record = parent::format($record);

        if (isset($record['extra']['user_id'])) {
            return sprintf(
                '%s {"app_url": "%s","request_id":"%s","user_id":"%s"}',
                $record['message'],
                config('app.url'),
                $record['extra']['request_id'],
                $record['extra']['user_id']
            );
        }

        return sprintf(
            '%s {"request_id":"%s"}',
            $record['message'],
            $record['extra']['request_id']
        );
    }
}
