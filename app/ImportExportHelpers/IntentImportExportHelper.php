<?php


namespace App\ImportExportHelpers;

use Illuminate\Contracts\Filesystem\FileNotFoundException;

class IntentImportExportHelper extends BaseImportExportHelper
{
    const INTENT_RESOURCE_DIRECTORY = 'intents';
    const INTENT_FILE_ROOT_ELEMENT = 'intent';
    const INTENT_FILE_EXTENSION = ".intent.xml";

    /**
     * @return string
     */
    public static function getIntentsPath(): string
    {
        return self::INTENT_RESOURCE_DIRECTORY;
    }

    /**
     * @param string $intentFileName
     * @return string
     */
    public static function getIntentPath(string $intentFileName): string
    {
        return self::getIntentsPath() . "/$intentFileName";
    }

    /**
     * @param string $intentFileName
     * @param string $data
     */
    public static function createIntentFile(string $intentFileName, string $data): void
    {
        self::getDisk()->put("/intents/$intentFileName", $data);
    }

    /**
     * @param $intentFileName
     * @return string
     * @throws FileNotFoundException
     */
    public static function getIntentFileData($intentFileName): string
    {
        return self::getDisk()->get($intentFileName);
    }

    /**
     * @return array|false
     */
    public static function getIntentFiles()
    {
        return preg_grep('/^([^.])/', self::getDisk()->files(self::getIntentsPath()));
    }

    /**
     * @param $intentFileName
     */
    public static function deleteIntentFile($intentFileName): void
    {
        self::getDisk()->delete($intentFileName);
    }

    /**
     * @param $intentFileName
     * @return string
     */
    public static function addIntentFileExtension($intentFileName): string
    {
        return $intentFileName . self::INTENT_FILE_EXTENSION;
    }
}
