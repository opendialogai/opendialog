<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use OpenDialogAi\ResponseEngine\MessageTemplate;
use OpenDialogAi\ResponseEngine\OutgoingIntent;

class UpdateMessages extends Command
{
    protected $signature = 'messages:update {outgoingIntent} {--y|yes}';

    protected $description = 'Update a specific outgoing intent and it own message templates';

    public function handle()
    {
        $outgoingIntentName = $this->argument('outgoingIntent');

        if ($this->option("yes")) {
            $continue = true;
        } else {
            $continue = $this->confirm(
                sprintf(
                    'This will update %s outgoing intent and it own messages. Are you sure you want to continue?',
                    $outgoingIntentName
                )
            );
        }

        if ($continue) {
            $this->importOutgoingIntent($outgoingIntentName);

            $this->info('Imports finished');
        } else {
            $this->info('OK, not running');
        }
    }

    protected function importOutgoingIntent($outgoingIntentName): void
    {
        $this->info(sprintf('Importing outgoing intent %s', $outgoingIntentName));

        $filename = base_path("resources/messages/$outgoingIntentName");
        $data = json_decode(file_get_contents($filename));

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
