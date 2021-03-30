<?php

namespace App\Console\Commands\Specification;

use App\ImportExportHelpers\ScenarioImportExportHelper;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use OpenDialogAi\Core\Conversation\Exceptions\DuplicateConversationObjectOdIdException;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;

class ImportScenarios extends Command
{
    protected $signature = 'scenarios:import';

    protected $description = 'Imports all Scenarios.';

    public function handle()
    {
        $fileNames = ScenarioImportExportHelper::getScenarioFiles();
        $this->info("Importing scenarios...");
        foreach ($fileNames as $fileName) {
            $this->importScenarioFromFile($fileName);
        }
        $this->info('Import complete!');
    }

    protected function importScenarioFromFile(string $filePath): void
    {
        $this->info(sprintf("Importing scenario from file %s...", $filePath));
        try {
            $scenarioData = ScenarioImportExportHelper::getScenarioFileData($filePath);
        } catch (FileNotFoundException $e) {
            $this->error(sprintf('No scenario file at %s', $filePath));
        }

        try {
            ScenarioImportExportHelper::importScenarioFromString($scenarioData);
        } catch (NotEncodableValueException $e) {
            $this->error(sprintf("Import of %s failed. Unable to decode file as json", $filePath));
        } catch (DuplicateConversationObjectOdIdException $e) {
            $this->warn(sprintf("An existing Scenario with odId %s already exists!. Skipping %s!", $e->getDuplicateOdId(),
                $filePath));
        }
    }

}
