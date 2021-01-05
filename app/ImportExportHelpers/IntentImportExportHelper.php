<?php


namespace App\ImportExportHelpers;

use App\ImportExportHelpers\Generator\IntentFileGenerator;
use App\ImportExportHelpers\Generator\InvalidFileFormatException;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use OpenDialogAi\ResponseEngine\OutgoingIntent;

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

    /**
     * @param $intentFileName
     * @return string
     */
    public static function removeIntentFileExtension($intentFileName): string
    {
        if (self::stringEndsWithFileExtension($intentFileName)) {
            return substr($intentFileName, 0, -1 * strlen(self::INTENT_FILE_EXTENSION));
        } else {
            return $intentFileName;
        }
    }

    /**
     * @param string $str
     * @return bool
     */
    public static function stringEndsWithFileExtension(string $str): bool
    {
        return substr($str, -1 * strlen(self::INTENT_FILE_EXTENSION)) == self::INTENT_FILE_EXTENSION;
    }

    /**
     * @param string $fileName
     * @param string $data
     * @return IntentFileGenerator
     * @throws InvalidFileFormatException
     */
    public static function importIntentFileFromString(string $fileName, string $data): IntentFileGenerator
    {
        try {
            $fileGenerator = IntentFileGenerator::fromString($data);
        } catch (InvalidFileFormatException $e) {
            throw new InvalidFileFormatException(sprintf('Invalid file formatting (%s) in %s', $e->getMessage(), $fileName));
        }

        $newIntent = OutgoingIntent::firstOrNew(['name' => $fileGenerator->getName()]);
        $newIntent->save();

        return $fileGenerator;
    }

    /**
     * @param Command|null $io
     */
    public static function deleteExistingIntents(Command $io = null): void
    {
        $outgoingIntents = OutgoingIntent::all();

        foreach ($outgoingIntents as $outgoingIntent) {
            $outgoingIntent->delete();

            is_null($io) ?: $io->info(sprintf('Deleted outgoing intent %s', $outgoingIntent->name));
        }
    }
}
