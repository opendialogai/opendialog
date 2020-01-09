<?php

namespace App\Logging;

use Monolog\Formatter\NormalizerFormatter;

class LogFormatter extends NormalizerFormatter
{
    public function __construct()
    {
        parent::__construct();
    }

    public function format(array $record)
    {
        $record = parent::format($record);
        return $this->getDocument($record);
    }

    /**
     * Convert a log message into a DB Log entity
     *
     * @param array $record
     * @return array
     */
    protected function getDocument(array $record)
    {
        if ($record['level_name'] != 'WARNING') {
            return [];
        }

        $document = $record['extra'];
        $document['message'] = $record['message'];
        $document['context'] = $record['context'];

        return $document;
    }
}
