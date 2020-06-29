<?php

namespace App\Logging;

use App\Warning;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class LogHandler extends AbstractProcessingHandler
{
    public function __construct($level = Logger::DEBUG)
    {
        parent::__construct($level);
    }

    protected function write(array $record): void
    {
        if (!empty($record['formatted'])) {
            $log = new Warning();
            $log->fill($record['formatted']);
            try {
                $log->save();
            } catch (QueryException $e) {
                Log::debug(sprintf('Warning was not persisted to the database: %s', $e->getMessage()));
            }
        }
    }

    protected function getDefaultFormatter(): FormatterInterface
    {
        return new LogFormatter();
    }
}
