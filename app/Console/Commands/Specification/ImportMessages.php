<?php

namespace App\Console\Commands\Specification;

use OpenDialogAi\ResponseEngine\MessageTemplate;
use OpenDialogAi\ResponseEngine\OutgoingIntent;

class ImportMessages extends BaseSpecificationCommand
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
                $this->importMessage($messageName . '.message');
            } else {
                $files = preg_grep('/^([^.])/', scandir(self::getMessagesPath()));

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
        $filename = self::getMessagePath($messageFileName);
        $data = file_get_contents($filename);

        preg_match('/<name>(.*?)<\/name>/s', $data, $matches);
        $messageName = $matches[1];
        $data = str_replace($matches[0], '', $data);

        $this->info(sprintf('Importing message %s', $messageName));

        preg_match('/<intent>(.*?)<\/intent>/s', $data, $matches);
        $intentName = $matches[1];
        $data = str_replace($matches[0], '', $data);

        preg_match('/<conditions>(.*?)<\/conditions>/s', $data, $matches);
        $condition = null;
        if ($matches) {
            $condition = $matches[1];
            $data = str_replace($matches[0], '', $data);
        }

        $this->info(sprintf('Adding/updating intent with name %s', $intentName));
        $newIntent = OutgoingIntent::firstOrNew(['name' => $intentName]);
        $newIntent->save();

        $this->info(sprintf('Adding/updating message template with name %s', $messageName));
        $message = MessageTemplate::firstOrNew(['name' => $messageName]);
        $message->conditions = trim($condition);
        $message->message_markup = trim($data);
        $message->outgoing_intent_id = $newIntent->id;
        $message->save();
    }
}
