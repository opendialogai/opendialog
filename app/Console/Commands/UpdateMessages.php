<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use OpenDialogAi\ResponseEngine\MessageTemplate;
use OpenDialogAi\ResponseEngine\OutgoingIntent;

class UpdateMessages extends Command
{
    protected $signature = 'messages:update {outgoingIntent?} {--y|yes}';

    protected $description = 'Update message templates';

    public function handle()
    {
        $outgoingIntentName = $this->argument('outgoingIntent');

        if ($this->option("yes")) {
            $continue = true;
        } else {
            $continue = $this->confirm('This will update messages. Are you sure you want to continue?');
        }

        if ($continue) {
            $files = preg_grep('/^([^.])/', scandir(base_path('resources/messages')));

            foreach ($files as $messageName) {
                $this->importMessage($messageName, $outgoingIntentName);
            }

            $this->info('Imports finished');
        } else {
            $this->info('OK, not running');
        }
    }

    protected function importMessage($messageFileName, $outgoingIntentName): void
    {
        $messageName = preg_replace('/.message$/', '', $messageFileName);

        $filename = base_path("resources/messages/$messageFileName");
        $data = file_get_contents($filename);

        preg_match('/<intent>(.*?)<\/intent>/s', $data, $matches);
        $intentName = $matches[1];
        $data = str_replace($matches[0], '', $data);

        if ($outgoingIntentName == null || $outgoingIntentName == $intentName) {
            $this->info(sprintf('Importing message %s', $messageName));

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
}
