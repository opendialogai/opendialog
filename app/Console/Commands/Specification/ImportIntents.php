<?php

namespace App\Console\Commands\Specification;

use App\ImportExportHelpers\Generator\IntentFileGenerator;
use App\ImportExportHelpers\Generator\InvalidFileFormatException;
use App\ImportExportHelpers\IntentImportExportHelper;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use OpenDialogAi\ResponseEngine\OutgoingIntent;

class ImportIntents extends Command
{
    protected $signature = 'intents:import {outgoingIntent?} {--y|yes}';

    protected $description = 'Sets up all intents';

    public function handle()
    {
        $outgoingIntentName = $this->argument('outgoingIntent');

        if ($this->option('yes')) {
            $continue = true;
        } else {
            $continue = $this->confirm(
                'This will import or update all intents. Are you sure you want to continue?'
            );
        }

        if ($continue) {
            if ($outgoingIntentName) {
                $intentFileNameWithExtension = IntentImportExportHelper::addIntentFileExtension($outgoingIntentName);
                $filePath = IntentImportExportHelper::getIntentPath($intentFileNameWithExtension);
                $this->importOutgoingIntent($filePath);
            } else {
                $files = IntentImportExportHelper::getIntentFiles();

                foreach ($files as $messageName) {
                    $this->importOutgoingIntent($messageName);
                }
            }

            $this->info('Import of intents finished');
        } else {
            $this->info('Bye');
        }
    }

    protected function importOutgoingIntent($outgoingIntentFileName): void
    {
        try {
            $data = IntentImportExportHelper::getIntentFileData($outgoingIntentFileName);
        } catch (FileNotFoundException $e) {
            $this->error(sprintf('Could not find intent at %s', $outgoingIntentFileName));
            return;
        }

        try {
            $fileGenerator = IntentFileGenerator::fromString($data);
        } catch (InvalidFileFormatException $e) {
            $this->error(sprintf('Invalid file formatting (%s) in %s', $e->getMessage(), $outgoingIntentFileName));
            return;
        }

        $this->info(sprintf('Importing intent %s', $fileGenerator->getName()));

        $newIntent = OutgoingIntent::firstOrNew(['name' => $fileGenerator->getName()]);
        $newIntent->save();
    }
}
