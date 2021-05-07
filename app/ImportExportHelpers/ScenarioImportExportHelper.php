<?php


namespace App\ImportExportHelpers;

use App\Console\Facades\ImportExportSerializer;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\Normalizers\ImportExport\ScenarioNormalizer;
use OpenDialogAi\Core\Conversation\Exceptions\DuplicateConversationObjectOdIdException;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\Turn;

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
        $hasPathsToSubstitute = PathSubstitutionHelper::stringContainsPaths($data);

        $serializerContext = [];

        if ($hasPathsToSubstitute) {
            $serializerContext = [
                ScenarioNormalizer::IGNORE_OBJECTS_WITH_POTENTIAL_PATH_VALUES => true,
            ];
        }

        /* @var $importingScenario Scenario */
        $importingScenario = ImportExportSerializer::deserialize($data, Scenario::class, 'json', $serializerContext);

        $existingScenarios = ConversationDataClient::getAllScenarios(false);

        $duplicateScenarios = $existingScenarios->filter(
            fn ($scenario) => $scenario->getOdId() === $importingScenario->getOdId()
        );
        if ($duplicateScenarios->count() > 0) {
            throw new DuplicateConversationObjectOdIdException(
                $importingScenario->getOdId(),
                sprintf(
                    "Cannot import scenario with odId %s. A scenario with that odId already exists!",
                    $importingScenario->getOdId()
                )
            );
        }

        $persistedScenario = ConversationDataClient::addFullScenarioGraph($importingScenario);

        if (!$hasPathsToSubstitute) {
            return $persistedScenario;
        }

        $map = PathSubstitutionHelper::createConversationObjectUidToPathMap($persistedScenario);

        // Deserialize WITH objects with potential path values and substitute the paths for the UIDs
        /** @var Scenario $scenarioWithPathsSubstituted */
        $scenarioWithPathsSubstituted = ImportExportSerializer::deserialize($data, Scenario::class, 'json', [
            ScenarioNormalizer::UID_MAP => $map
        ]);

        if (PathSubstitutionHelper::shouldPatch($scenarioWithPathsSubstituted)) {
            $scenarioPatch = PathSubstitutionHelper::createPatch($persistedScenario->getUid(), $scenarioWithPathsSubstituted);
            ConversationDataClient::updateScenario($scenarioPatch);
        }

        foreach ($scenarioWithPathsSubstituted->getConversations() as $cIdx => $conversation) {
            /** @var Conversation $conversation */

            /** @var Conversation $persistedConversation */
            $persistedConversation = $persistedScenario->getConversations()[$cIdx];

            if (PathSubstitutionHelper::shouldPatch($conversation)) {
                $conversationPatch = PathSubstitutionHelper::createPatch($persistedConversation->getUid(), $conversation);
                ConversationDataClient::updateConversation($conversationPatch);
            }

            foreach ($conversation->getScenes() as $sIdx => $scene) {
                /** @var Scene $scene */

                /** @var Scene $persistedScene */
                $persistedScene = $persistedConversation->getScenes()[$sIdx];

                if (PathSubstitutionHelper::shouldPatch($scene)) {
                    $scenePatch = PathSubstitutionHelper::createPatch($persistedScene->getUid(), $scene);
                    ConversationDataClient::updateScene($scenePatch);
                }

                foreach ($scene->getTurns() as $tIdx => $turn) {
                    /** @var Turn $turn */

                    /** @var Turn $persistedTurn */
                    $persistedTurn = $persistedScene->getTurns()[$tIdx];

                    if (PathSubstitutionHelper::shouldPatch($turn)) {
                        $turnPatch = PathSubstitutionHelper::createPatch($persistedTurn->getUid(), $turn);
                        ConversationDataClient::updateTurn($turnPatch);
                    }

                    foreach ($turn->getRequestIntents() as $iIdx => $intent) {
                        /** @var Intent $intent */

                        /** @var Intent $persistedIntent */
                        $persistedIntent = $persistedTurn->getRequestIntents()[$iIdx];

                        if (PathSubstitutionHelper::shouldPatch($intent)) {
                            $intentPatch = PathSubstitutionHelper::createPatch($persistedIntent->getUid(), $intent);
                            ConversationDataClient::updateIntent($intentPatch);
                        }
                    }

                    foreach ($turn->getResponseIntents() as $iIdx => $intent) {
                        /** @var Turn $intent */

                        /** @var Intent $persistedIntent */
                        $persistedIntent = $persistedTurn->getResponseIntents()[$iIdx];

                        if (PathSubstitutionHelper::shouldPatch($intent)) {
                            $intentPatch = PathSubstitutionHelper::createPatch($persistedIntent->getUid(), $intent);
                            ConversationDataClient::updateIntent($intentPatch);
                        }
                    }
                }
            }
        }

        return ConversationDataClient::getFullScenarioGraph($persistedScenario->getUid());
    }
}
