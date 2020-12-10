<?php


namespace App\ImportExportHelpers;

use Illuminate\Contracts\Filesystem\FileNotFoundException;

class MessageImportExportHelper extends BaseImportExportHelper
{
    const MESSAGE_RESOURCE_DIRECTORY = 'messages';
    const MESSAGE_FILE_ROOT_ELEMENT = 'message-template';
    const MESSAGE_FILE_EXTENSION = ".message.xml";

    /**
     * @return string
     */
    public static function getMessagesPath(): string
    {
        return self::MESSAGE_RESOURCE_DIRECTORY;
    }

    /**
     * @param string $messageFileName
     * @return string
     */
    public static function getMessagePath(string $messageFileName): string
    {
        return self::getMessagesPath() . "/$messageFileName";
    }

    /**
     * @param string $messageFileName
     * @param string $data
     */
    public static function createMessageFile(string $messageFileName, string $data): void
    {
        self::getDisk()->put("/messages/$messageFileName", $data);
    }

    /**
     * @param string $filename
     * @return string
     * @throws FileNotFoundException
     */
    public static function getMessageFileData(string $filename): string
    {
        return self::getDisk()->get($filename);
    }

    /**
     * @return array|false
     */
    public static function getMessageFiles()
    {
        return preg_grep('/^([^.])/', self::getDisk()->files(self::getMessagesPath()));
    }

    /**
     * @param $messageFileName
     */
    public static function deleteMessageFile($messageFileName): void
    {
        self::getDisk()->delete($messageFileName);
    }

    /**
     * @param $messageFileName
     * @return string
     */
    public static function addMessageFileExtension($messageFileName): string
    {
        return $messageFileName . self::MESSAGE_FILE_EXTENSION;
    }

    /**
     * @param $messageFileName
     * @return string
     */
    public static function removeMessageFileExtension($messageFileName): string
    {
        if (self::stringEndsWithFileExtension($messageFileName)) {
            return substr($messageFileName, 0, -1 * strlen(self::MESSAGE_FILE_EXTENSION));
        } else {
            return $messageFileName;
        }
    }

    /**
     * @param string $str
     * @return bool
     */
    public static function stringEndsWithFileExtension(string $str): bool
    {
        return substr($str, -1 * strlen(self::MESSAGE_FILE_EXTENSION)) == self::MESSAGE_FILE_EXTENSION;
    }
}
