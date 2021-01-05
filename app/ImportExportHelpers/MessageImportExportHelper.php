<?php


namespace App\ImportExportHelpers;

use App\ImportExportHelpers\Generator\InvalidFileFormatException;
use App\ImportExportHelpers\Generator\MessageFileGenerator;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use OpenDialogAi\ResponseEngine\MessageTemplate;
use OpenDialogAi\ResponseEngine\OutgoingIntent;

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

    /**
     * @param string $messageFileName
     * @param string $data
     * @param Command|null $io
     * @return MessageFileGenerator
     * @throws InvalidFileFormatException
     */
    public static function importMessageFileFromString(
        string $messageFileName,
        string $data,
        Command $io = null
    ): MessageFileGenerator {
        try {
            $messageFileGenerator = MessageFileGenerator::fromString($data);
        } catch (InvalidFileFormatException $e) {
            throw new InvalidFileFormatException(sprintf(
                'Invalid file formatting (%s) in %s',
                $e->getMessage(),
                $messageFileName
            ));
        }

        is_null($io) ?: $io->info(sprintf('Adding/updating intent with name %s', $messageFileGenerator->getIntent()));
        $newIntent = OutgoingIntent::firstOrNew(['name' => $messageFileGenerator->getIntent()]);
        $newIntent->save();

        is_null($io) ?: $io->info(sprintf('Adding/updating message template with name %s', $messageFileGenerator->getName()));
        $message = MessageTemplate::firstOrNew(['name' => $messageFileGenerator->getName()]);
        $message->conditions = trim($messageFileGenerator->getConditions());
        $message->message_markup = trim($messageFileGenerator->getMarkup());
        $message->outgoing_intent_id = $newIntent->id;
        $message->save();

        return $messageFileGenerator;
    }

    /**
     * @param Command|null $io
     */
    public static function deleteExistingMessages(Command $io = null): void
    {
        $messageTemplates = MessageTemplate::all();

        foreach ($messageTemplates as $messageTemplate) {
            $messageTemplate->delete();

            is_null($io) ?: $io->info(sprintf('Deleted outgoing intent %s', $messageTemplate->name));
        }
    }
}
