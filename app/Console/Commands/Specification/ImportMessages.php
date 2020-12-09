<?php

namespace App\Console\Commands\Specification;

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

        $xml = new \SimpleXMLElement($data);
        $messageName = $xml->name;

        $this->info(sprintf('Importing message %s', $messageName));

        $intentName = $xml->intent;
        $condition = $xml->conditions;
        $markup = $xml->markup->message->asXML();

        $this->info(sprintf('Adding/updating intent with name %s', $intentName));
        $newIntent = OutgoingIntent::firstOrNew(['name' => $intentName]);
        $newIntent->save();

        $this->info(sprintf('Adding/updating message template with name %s', $messageName));
        $message = MessageTemplate::firstOrNew(['name' => $messageName]);
        $message->conditions = trim($condition);
        $message->message_markup = trim($markup);
        $message->outgoing_intent_id = $newIntent->id;
        $message->save();
    }
}
