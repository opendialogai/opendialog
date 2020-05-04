<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use OpenDialogAi\ResponseEngine\MessageTemplate;
use OpenDialogAi\ResponseEngine\OutgoingIntent;

class SetUpMessages extends Command
{
    protected $signature = 'messages:setup';

    protected $description = 'Sets up all messages';

    public function handle()
    {
        $continue = $this->confirm(
            'This will import or update all messages. Are you sure you want to continue?'
        );

        if ($continue) {
            $files = preg_grep('/^([^.])/', scandir('resources/messages'));

            foreach ($files as $messageName) {
                $this->importOutgoingIntent($messageName);
            }
        }
    }

    protected function importOutgoingIntent($outgoingIntentName): void
    {
        $this->info(sprintf('Importing outgoing intent %s', $outgoingIntentName));

        $data = json_decode(file_get_contents("resources/messages/$outgoingIntentName"));

        $this->info(sprintf('Adding/updating intent with name %s', $data->outgoingIntent));
        $newIntent = OutgoingIntent::firstOrNew(['name' => $data->outgoingIntent]);
        $newIntent->save();

        foreach ($data->messageTemplates as $messageName => $messageTemplate) {
            $this->info(sprintf('Adding/updating message template with name %s', $messageName));
            $message = MessageTemplate::firstOrNew(['name' => $messageName]);
            $message->fill((array) $messageTemplate);
            $message->outgoing_intent_id = $newIntent->id;
            $message->save();
        }
    }
}
