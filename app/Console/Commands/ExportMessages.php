<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use OpenDialogAi\ResponseEngine\OutgoingIntent;

class ExportMessages extends Command
{
    protected $signature = 'messages:export {outgoingIntent?}';

    protected $description = 'Export all message templates';

    public function handle()
    {
        $outgoingIntentName = $this->argument('outgoingIntent');

        if ($outgoingIntentName) {
            $continue = $this->confirm(
                sprintf(
                    'Do you want to export %s outgoing intent and it own messages?',
                    $outgoingIntentName
                )
            );
        } else {
            $continue = $this->confirm('Do you want to export all outgoing intents and messages?');
        }

        if ($continue) {
            $outgoingIntents = OutgoingIntent::all();

            foreach ($outgoingIntents as $outgoingIntent) {
                $this->exportoutgoingIntent($outgoingIntent);
            }

            $this->info('Exports finished');
        } else {
            $this->info('Bye');
        }
    }

    protected function exportoutgoingIntent(OutgoingIntent $outgoingIntent): void
    {
        $this->info(sprintf('Exporting outgoing intent %s', $outgoingIntent->name));

        $messageTemplates = [];
        foreach ($outgoingIntent->messageTemplates as $messageTemplate) {
            $messageTemplates[$messageTemplate->name] = [
                'conditions' => $messageTemplate->conditions,
                'message_markup' => $messageTemplate->message_markup,
            ];
        }

        $output = json_encode([
            'outgoingIntent' => $outgoingIntent->name,
            'messageTemplates' => $messageTemplates,
        ]);

        $filename = "resources/messages/$outgoingIntent->name";
        file_put_contents($filename, $output);
    }
}
