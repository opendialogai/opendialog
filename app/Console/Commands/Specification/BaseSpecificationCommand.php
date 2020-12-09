<?php


namespace App\Console\Commands\Specification;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

abstract class BaseSpecificationCommand extends Command
{
    const SPECIFICATIONS_DISK_NAME = 'specifications';
    const CONVERSATION_RESOURCE_DIRECTORY = 'conversations';
    const INTENT_RESOURCE_DIRECTORY = 'intents';
    const MESSAGE_RESOURCE_DIRECTORY = 'messages';

    /**
     * @return Filesystem
     */
    public static function getDisk(): Filesystem
    {
        return Storage::disk(self::SPECIFICATIONS_DISK_NAME);
    }

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
        return preg_replace(['/.conv.yml$/', '/^conversations\//'], '', $conversationFileName);
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
    protected function getIntentFileData($intentFileName): string
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
}
