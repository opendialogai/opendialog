<?php

namespace App\Console\Commands\Specification;

use App\ImportExportHelpers\Generator\InvalidFileFormatException;
use App\ImportExportHelpers\IntentImportExportHelper;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class ImportIntents extends Command
{
    protected $signature = 'intents:import {outgoingIntent?} {--y|yes}';

    protected $description = 'Imports all intents.';

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
                $this->importOutgoingIntentFromFile($filePath);
            } else {
                $files = IntentImportExportHelper::getIntentFiles();

                foreach ($files as $messageName) {
                    $this->importOutgoingIntentFromFile($messageName);
                }
            }

            $this->info('Import of intents finished');
        } else {
            $this->info('Bye');
        }
    }

    protected function importOutgoingIntentFromFile($outgoingIntentFileName): void
    {
        try {
            $data = IntentImportExportHelper::getIntentFileData($outgoingIntentFileName);
        } catch (FileNotFoundException $e) {
            $this->error(sprintf('Could not find intent at %s', $outgoingIntentFileName));
            return;
        }

        try {
            $fileGenerator = IntentImportExportHelper::importIntentFileFromString($outgoingIntentFileName, $data);
            $this->info(sprintf('Importing intent %s', $fileGenerator->getName()));
        } catch (InvalidFileFormatException $e) {
            $this->error($e->getMessage());
            return;
        }
    }
}
