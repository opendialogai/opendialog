<?php


namespace App\ImportExportHelpers;


use App\Console\Facades\ImportExportSerializer;
use OpenDialogAi\Core\Conversation\Exceptions\DuplicateConversationObjectOdIdException;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\Scenario;

class ScenarioImportExportHelper extends BaseImportExportHelper
{
    const SCENARIO_RESOURCE_DIRECTORY = 'scenarios';
    const SCENARIO_FILE_EXTENSION = ".scenario.json";

    /**
     * @return string
     */
    public static function getScenariosPath(): string
    {
        return self::SCENARIO_RESOURCE_DIRECTORY;
    }

    /**
     * @param  string  $name
     *
     * @return string
     */
    public static function suffixScenarioFileName(string $name): string
    {
        return $name.self::SCENARIO_FILE_EXTENSION;
    }

    /**
     * @param  string  $fileName
     *
     * @return string
     */
    public static function prefixScenariosPath(string $fileName): string
    {
        return self::getScenariosPath()."/$fileName";
    }

    public static function getScenarioFilePath(string $odId): string
    {
        return self::prefixScenariosPath(self::suffixScenarioFileName($odId));
    }

    /**
     * @param  string  $filePath
     * @param  string  $data
     */
    public static function createScenarioFile(string $filePath, string $data): void
    {
        self::getDisk()->put($filePath, $data);
    }

    /**
     * @param  string  $filePath
     */
    public static function deleteScenarioFile(string $filePath): void
    {
        self::getDisk()->delete($filePath);
    }

    /**
     * @return array|false
     */
    public static function getScenarioFiles()
    {
        $files = self::getDisk()->files(self::getScenariosPath());
        return preg_grep('/^([^.])/', $files);
    }


    /**
     * Read a scenario file
     *
     * @param  string  $filePath
     *
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public static function getScenarioFileData(string $filePath)
    {
        return self::getDisk()->get($filePath);
    }

    public static function scenarioFileExists(string $filePath)
    {
        return self::getDisk()->exists($filePath);
    }

    /**
     * @param  string  $data
     *
     * @return Scenario
     */
    public static function importScenarioFromString(string $data): Scenario
    {
        /* @var $importingScenario Scenario */
        $importingScenario = ImportExportSerializer::deserialize($data, Scenario::class, 'json');

        $existingScenarios = ConversationDataClient::getAllScenarios(false);

        $duplicateScenarios = $existingScenarios->filter(fn($scenario) => $scenario->getOdId() === $importingScenario->getOdId());
        if ($duplicateScenarios->count() > 0) {
            throw new DuplicateConversationObjectOdIdException( $importingScenario->getOdId(),
                sprintf("Cannot import scenario with odId %s. A scenario with that odId already exists!",
                $importingScenario->getOdId()));
        }
        return ConversationDataClient::addFullScenarioGraph($importingScenario);
    }
}

