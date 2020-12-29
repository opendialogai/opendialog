<?php

namespace App\Console\Commands\Specification;

use App\ImportExportHelpers\Generator\InvalidFileFormatException;
use App\ImportExportHelpers\MessageImportExportHelper;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class ImportMessages extends Command
{
    protected $signature = 'messages:import {message?} {--y|yes}';

    protected $description = 'Imports all messages.';

    public function handle()
    {
        $messageName = $this->argument('message');

        if ($this->option('yes')) {
            $continue = true;
        } elseif ($messageName) {
            $continue = $this->confirm(
                sprintf(
                    'Do you want to import message %s?',
                    $messageName
                )
            );
        } else {
            $continue = $this->confirm(
                'This will import or update all messages. Are you sure you want to continue?'
            );
        }

        if ($continue) {
            if ($messageName) {
                $messageFileNameWithExtension = MessageImportExportHelper::addMessageFileExtension($messageName);
                $filePath = MessageImportExportHelper::getMessagePath($messageFileNameWithExtension);
                $this->importMessageFromFile($filePath);
            } else {
                $files = MessageImportExportHelper::getMessageFiles();

                foreach ($files as $messageName) {
                    $this->importMessageFromFile($messageName);
                }
            }

            $this->info('Import of messages finished');
        } else {
            $this->info('Bye');
        }
    }

    protected function importMessageFromFile($messageFileName): void
    {
        try {
            $data = MessageImportExportHelper::getMessageFileData($messageFileName);
        } catch (FileNotFoundException $e) {
            $this->error(sprintf('Could not find message at %s', $messageFileName));
            return;
        }

        try {
            $fileGenerator = MessageImportExportHelper::importMessageFileFromString($messageFileName, $data, $this);
            $this->info(sprintf('Importing message %s', $fileGenerator->getName()));
        } catch (InvalidFileFormatException $e) {
            $this->error($e->getMessage());
            return;
        }
    }
}
