<?php


namespace App\Console\Commands\Specification;

use Illuminate\Console\Command;

abstract class BaseSpecificationCommand extends Command
{
    const CONVERSATION_RESOURCE_PATH = 'specifications/conversations';
    const INTENT_RESOURCE_PATH = 'specifications/intents';
    const MESSAGE_RESOURCE_PATH = 'specifications/messages';

    /**
     * @return string
     */
    public static function getConversationsPath(): string
    {
        return resource_path(self::CONVERSATION_RESOURCE_PATH);
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
        return preg_replace('/.conv$/', '', $conversationFileName);
    }

    /**
     * @return string
     */
    public static function getIntentsPath(): string
    {
        return resource_path(self::INTENT_RESOURCE_PATH);
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
     * @param string $data
     * @return string
     */
    public static function getIntentNameFromIntentXml(string $data): string
    {
        preg_match('/<intent>(.*?)<\/intent>/s', $data, $matches);
        return $matches[1];
    }

    /**
     * @return string
     */
    public static function getMessagesPath(): string
    {
        return resource_path(self::MESSAGE_RESOURCE_PATH);
    }

    /**
     * @param string $messageFileName
     * @return string
     */
    public static function getMessagePath(string $messageFileName): string
    {
        return self::getMessagesPath() . "/$messageFileName";
    }
}
