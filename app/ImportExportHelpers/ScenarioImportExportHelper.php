<?php


namespace App\ImportExportHelpers;


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
     * @param  string  $scenarioFileName
     *
     * @return string
     */
    public static function getScenarioPath(string $scenarioFileName): string
    {
        return self::getScenariosPath()."/$scenarioFileName";
    }

    /**
     * @param  string  $scenarioFileName
     * @param  string  $data
     */
    public static function createScenarioFile(string $scenarioFileName, string $data): void
    {
        self::getDisk()->put(sprintf("/%s/%s", self::SCENARIO_RESOURCE_DIRECTORY, $scenarioFileName), $data);
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
     * @param  string  $name
     *
     * @return string
     */
    public static function fileName(string $name): string
    {
        return $name.self::SCENARIO_FILE_EXTENSION;
    }

    public static function getFilePath(string $name): string
    {
        return self::getScenarioPath(self::fileName($name));
    }
}

