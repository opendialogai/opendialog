<?php


namespace App\ImportExportHelpers;

use App\Http\Controllers\API\ConversationsController;
use App\ImportExportHelpers\Generator\InvalidFileFormatException;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use OpenDialogAi\ConversationBuilder\Conversation;
use OpenDialogAi\Core\Conversation\Conversation as ConversationNode;

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

    /**
     * @param $conversationFileName
     * @return string
     */
    public static function removeConversationFileExtension($conversationFileName): string
    {
        if (self::stringEndsWithFileExtension($conversationFileName)) {
            return substr($conversationFileName, 0, -1 * strlen(self::CONVERSATION_FILE_EXTENSION));
        } else {
            return $conversationFileName;
        }
    }

    /**
     * @param string $str
     * @return bool
     */
    public static function stringEndsWithFileExtension(string $str): bool
    {
        return substr($str, -1 * strlen(self::CONVERSATION_FILE_EXTENSION)) == self::CONVERSATION_FILE_EXTENSION;
    }

    /**
     * @param string $conversationName
     * @param string $model
     * @param bool $activate
     * @param Command|null $io
     * @return Conversation
     * @throws InvalidFileFormatException
     */
    public static function importConversationFromString(
        string $conversationName,
        string $model,
        bool $activate = false,
        Command $io = null
    ): Conversation {
        if ($error = resolve(ConversationsController::class)->validateValue($model)) {
            throw new InvalidFileFormatException(sprintf(
                'Invalid file formatting (%s) in %s',
                $error['message'],
                $conversationName
            ));
        }

        $newConversation = Conversation::firstOrNew(['name' => $conversationName]);
        $newConversation->status = ConversationNode::SAVED;
        $newConversation->fill(['model' => $model]);
        $newConversation->save();

        if ($activate) {
            is_null($io) ?: $io->info(sprintf('Activating conversation with name %s', $newConversation->name));
            $newConversation->activateConversation();
        }

        return $newConversation;
    }

    /**
     * @param Command|null $io
     */
    public static function deleteExistingConversations(Command $io = null): void
    {
        $conversations = Conversation::all();

        foreach ($conversations as $conversation) {
            self::deleteExistingConversation($conversation);

            is_null($io) ?: $io->info(sprintf('Deleted conversation %s', $conversation->name));
        }
    }

    /**
     * @param Conversation $conversation
     */
    public static function deleteExistingConversation(Conversation $conversation): void
    {
        if ($conversation->status == ConversationNode::ACTIVATED) {
            $conversation->deactivateConversation();
            $conversation->archiveConversation();
        } elseif ($conversation->status == ConversationNode::DEACTIVATED) {
            $conversation->archiveConversation();
        }

        $conversation->delete();
    }
}
