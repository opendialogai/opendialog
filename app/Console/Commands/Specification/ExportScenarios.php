<?php


namespace App\Console\Commands\Specification;

use App\Console\Facades\ImportExportSerializer;
use App\ImportExportHelpers\PathSubstitutionHelper;
use App\ImportExportHelpers\ScenarioImportExportHelper;
use Illuminate\Console\Command;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\Normalizers\ImportExport\ScenarioNormalizer;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\Scenario;

class ExportScenarios extends Command
{
    protected $signature = 'scenarios:export';

    protected $description = 'Exports all scenarios.';

    public function handle()
    {
        $this->info('Beginning scenarios export...');
        $scenarios = ConversationDataClient::getAllScenarios();
        foreach ($scenarios as $scenario) {
            $fullScenarioGraph = ConversationDataClient::getFullScenarioGraph($scenario->getUid());
            $this->exportScenario($fullScenarioGraph);
        }
        $this->info('Export complete!');
    }

    public function exportScenario(Scenario $fullScenarioGraph)
    {
        $filePath = ScenarioImportExportHelper::getScenarioFilePath($fullScenarioGraph->getOdId());
        $serialized = ImportExportSerializer::serialize($fullScenarioGraph, 'json', [
            ScenarioNormalizer::UID_MAP => PathSubstitutionHelper::createConversationObjectUidToPathMap($fullScenarioGraph)
        ]);

        if (ScenarioImportExportHelper::scenarioFileExists($filePath)) {
            $this->info(sprintf("Scenario file at %s already exists. Deleting...", $filePath));
            ScenarioImportExportHelper::deleteScenarioFile($filePath);
        }
        $this->info(sprintf('Exporting scenario \'%s\' to %s.', $fullScenarioGraph->getOdId(), $filePath));
        ScenarioImportExportHelper::createScenarioFile($filePath, $serialized);
    }
}
