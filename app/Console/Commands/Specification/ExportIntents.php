<?php

namespace App\Console\Commands\Specification;

use App\ImportExportHelpers\Generator\IntentFileGenerator;
use App\ImportExportHelpers\IntentImportExportHelper;
use Illuminate\Console\Command;
use OpenDialogAi\ResponseEngine\OutgoingIntent;

class ExportIntents extends Command
{
    protected $signature = 'intents:export {outgoingIntent?} {--y|yes}';

    protected $description = 'Export all outgoing intents.';

    public function handle()
    {
        $outgoingIntentName = $this->argument('outgoingIntent');

        if ($this->option('yes')) {
            $continue = true;
        } elseif ($outgoingIntentName) {
            $continue = $this->confirm(
                sprintf(
                    'Do you want to export %s outgoing intent?',
                    $outgoingIntentName
                )
            );
        } else {
            $continue = $this->confirm('Do you want to export all outgoing intents?');
        }

        if ($continue) {
            if ($outgoingIntentName) {
                $outgoingIntent = OutgoingIntent::where('name', $outgoingIntentName)->first();

                if (is_null($outgoingIntent)) {
                    $this->error(sprintf('%s doesn\'t exist.', $outgoingIntentName));
                    return;
                } else {
                    $this->exportOutgoingIntent($outgoingIntent);
                }
            } else {
                $outgoingIntents = OutgoingIntent::all();

                foreach ($outgoingIntents as $outgoingIntent) {
                    $this->exportOutgoingIntent($outgoingIntent);
                }
            }

            $this->info('Export of intents finished');
        } else {
            $this->info('Bye');
        }
    }

    protected function exportOutgoingIntent(OutgoingIntent $outgoingIntent): void
    {
        $this->info(sprintf('Exporting outgoing intent %s', $outgoingIntent->name));

        $output = new IntentFileGenerator($outgoingIntent->name);

        $intentFileName = IntentImportExportHelper::addIntentFileExtension($outgoingIntent->name);
        IntentImportExportHelper::createIntentFile($intentFileName, $output);
    }
}
