<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use OpenDialogAi\ResponseEngine\OutgoingIntent;

class ExportMessages extends Command
{
    protected $signature = 'messages:export {outgoingIntent?} {--y|yes}';

    protected $description = 'Export all message templates';

    public function handle()
    {
        $outgoingIntentName = $this->argument('outgoingIntent');

        if ($this->option("yes")) {
            $continue = true;
        } elseif ($outgoingIntentName) {
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

        $intent = "<intent>" . $outgoingIntent->name . "</intent>\n";

        foreach ($outgoingIntent->messageTemplates as $messageTemplate) {
            $output = $intent;
            if ($messageTemplate->conditions) {
                $output .= "<conditions>\n" . $messageTemplate->conditions . "\n</conditions>\n";
            }
            $output .= $messageTemplate->message_markup;

            $filename = "resources/messages/$messageTemplate->name.message";
            file_put_contents($filename, $output);
        }

        $filename = "resources/messages/$outgoingIntent->name";
        file_put_contents($filename, $outgoingIntent->name);
    }
}
