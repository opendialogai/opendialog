<?php


namespace App\ImportExportHelpers;

use Illuminate\Contracts\Filesystem\FileNotFoundException;

class ConversationImportExportHelper extends BaseImportExportHelper
{
    const CONVERSATION_RESOURCE_DIRECTORY = 'conversations';
    const CONVERSATION_FILE_EXTENSION = ".conv.yml";

    /**
     * @return string
     */
    public static function getConversationsPath(): string
    {
        return self::CONVERSATION_RESOURCE_DIRECTORY;
    }

    /**
     * @param string $conversationFileName
     * @return string
     */
    public static function getConversationPath(string $conversationFileName): string
    {
        return self::getConversationsPath() . "/$conversationFileName";
    }

    /**
     * @param string $conversationFileName
     * @return string|null
     */
    public static function getConversationNameFromFileName(string $conversationFileName): ?string
    {
        return preg_replace(['/\.conv\.yml$/', '/^conversations\//'], '', $conversationFileName);
    }

    /**
     * @param string $conversationFileName
     * @param string $data
     */
    public static function createConversationFile(string $conversationFileName, string $data): void
    {
        self::getDisk()->put("/conversations/$conversationFileName", $data);
    }

    /**
     * @param string $filename
     * @return string
     * @throws FileNotFoundException
     */
    public static function getConversationFileData(string $filename): string
    {
        return self::getDisk()->get($filename);
    }

    /**
     * @return array|false
     */
    public static function getConversationFiles()
    {
        return preg_grep('/^([^.])/', self::getDisk()->files(self::getConversationsPath()));
    }

    /**
     * @param $conversationFileName
     */
    public static function deleteConversationFile($conversationFileName): void
    {
        self::getDisk()->delete($conversationFileName);
    }

    /**
     * @param $conversationFileName
     * @return string
     */
    public static function addConversationFileExtension($conversationFileName): string
    {
        return $conversationFileName . self::CONVERSATION_FILE_EXTENSION;
    }
}
