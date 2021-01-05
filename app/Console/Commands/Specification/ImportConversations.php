<?php

namespace App\Console\Commands\Specification;

use App\ImportExportHelpers\ConversationImportExportHelper;
use App\ImportExportHelpers\Generator\InvalidFileFormatException;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class ImportConversations extends Command
{
    protected $signature = 'conversations:import {conversation?} {--y|yes} {--a|activate}';

    protected $description = 'Imports all conversations.';

    public function handle()
    {
        $conversationName = $this->argument('conversation');

        if ($this->option('yes')) {
            $continue = true;
        } elseif ($conversationName) {
            $continue = $this->confirm(
                sprintf(
                    'Do you want to import conversation %s?',
                    $conversationName
                )
            );
        } else {
            $continue = $this->confirm(
                'This will import or update all conversations. Are you sure you want to continue?'
            );
        }

        if ($continue) {
            $activate = ($this->option('activate')) ? true : false;

            if ($conversationName) {
                $conversationFileName = ConversationImportExportHelper::addConversationFileExtension($conversationName);
                $filePath = ConversationImportExportHelper::getConversationPath($conversationFileName);
                $this->importConversationFromFile($filePath, $activate);
            } else {
                $files = ConversationImportExportHelper::getConversationFiles();

                foreach ($files as $conversationFileName) {
                    $this->importConversationFromFile($conversationFileName, $activate);
                }
            }

            $this->info('Import of conversations finished');
        } else {
            $this->info('OK, not running');
        }
    }

    protected function importConversationFromFile($conversationFileName, $activate): void
    {
        $conversationName = ConversationImportExportHelper::getConversationNameFromFileName($conversationFileName);

        $this->info(sprintf('Importing conversation %s', $conversationName));

        try {
            $model = ConversationImportExportHelper::getConversationFileData($conversationFileName);
        } catch (FileNotFoundException $e) {
            $this->error(sprintf('Could not find conversation at %s', $conversationFileName));
            return;
        }

        try {
            ConversationImportExportHelper::importConversationFromString($conversationName, $model, $activate, $this);
        } catch (InvalidFileFormatException $e) {
            $this->error($e->getMessage());
            return;
        }
    }
}
