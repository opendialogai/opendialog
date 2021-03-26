<?php


namespace App\Console\Commands\Specification;

use App\Console\Facades\ImportExportSerializer;
use App\ImportExportHelpers\ScenarioImportExportHelper;
use Illuminate\Console\Command;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\Scenario;

class ExportScenarios extends Command
{
    protected $signature = 'scenarios:export';

    protected $description = 'Exports all scenarios.';

    public function handle()
    {
        $this->info('Beginning scenarios export...');
        $scenarios = ConversationDataClient::getAllScenarios(false);
        foreach ($scenarios as $scenario) {
            $fullScenarioGraph = ConversationDataClient::getFullScenarioGraph($scenario->getUid());
            $this->exportScenario($fullScenarioGraph);
        }
        $this->info('Export complete!');
    }

    public function exportScenario(Scenario $fullScenarioGraph)
    {
        $fileName = ScenarioImportExportHelper::fileName($fullScenarioGraph->getOdId());
        $this->info(sprintf('Exporting scenario \'%s\' to %s.', $fullScenarioGraph->getName(), $fileName));
        $serialized = ImportExportSerializer::serialize($fullScenarioGraph, 'json');
        ScenarioImportExportHelper::createScenarioFile($fileName, $serialized);
    }
}
