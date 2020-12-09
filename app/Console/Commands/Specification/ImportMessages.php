<?php

namespace App\Console\Commands\Specification;

use App\ImportExportHelpers\Generator\InvalidFileFormatException;
use App\ImportExportHelpers\Generator\MessageFileGenerator;
use App\ImportExportHelpers\MessageImportExportHelper;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use OpenDialogAi\ResponseEngine\MessageTemplate;
use OpenDialogAi\ResponseEngine\OutgoingIntent;

class ImportMessages extends Command
{
    protected $signature = 'messages:import {message?} {--y|yes}';

    protected $description = 'Sets up all messages';

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
                $this->importMessage($filePath);
            } else {
                $files = MessageImportExportHelper::getMessageFiles();

                foreach ($files as $messageName) {
                    $this->importMessage($messageName);
                }
            }

            $this->info('Import of messages finished');
        } else {
            $this->info('Bye');
        }
    }

    protected function importMessage($messageFileName): void
    {
        try {
            $data = MessageImportExportHelper::getMessageFileData($messageFileName);
        } catch (FileNotFoundException $e) {
            $this->error(sprintf('Could not find message at %s', $messageFileName));
            return;
        }

        try {
            $messageFileGenerator = MessageFileGenerator::fromString($data);
        } catch (InvalidFileFormatException $e) {
            $this->error(sprintf('Invalid file formatting (%s) in %s', $e->getMessage(), $messageFileName));
            return;
        }

        $this->info(sprintf('Importing message %s', $messageFileGenerator->getName()));

        $this->info(sprintf('Adding/updating intent with name %s', $messageFileGenerator->getIntent()));
        $newIntent = OutgoingIntent::firstOrNew(['name' => $messageFileGenerator->getIntent()]);
        $newIntent->save();

        $this->info(sprintf('Adding/updating message template with name %s', $messageFileGenerator->getName()));
        $message = MessageTemplate::firstOrNew(['name' => $messageFileGenerator->getName()]);
        $message->conditions = trim($messageFileGenerator->getConditions());
        $message->message_markup = trim($messageFileGenerator->getMarkup());
        $message->outgoing_intent_id = $newIntent->id;
        $message->save();
    }
}
