<?php

namespace App\Console\Commands\Specification;

class ExportSpecification extends BaseSpecificationCommand
{
    protected $signature = 'specification:export {--y|yes}';

    protected $description = '';

    public function handle()
    {
        if ($this->option('yes')) {
            $continue = true;
        } else {
            $continue = $this->confirm('Do you want to remove all conversations, intents, messages and export the new ones?');
        }

        if ($continue) {
            $conversationFiles = self::getConversationFiles();
            foreach ($conversationFiles as $conversationFile) {
                if (is_file($conversationFile)) {
                    self::deleteConversationFile($conversationFile);
                }
            }

            $intentFiles = self::getIntentFiles();
            foreach ($intentFiles as $intentFile) {
                if (is_file($intentFile)) {
                    self::deleteIntentFile($intentFile);
                }
            }

            $messageFiles = self::getMessageFiles();
            foreach ($messageFiles as $messageFile) {
                if (is_file($messageFile)) {
                    self::deleteMessageFile($messageFile);
                }
            }

            $this->call(
                'conversations:export',
                [
                    '--yes' => true
                ]
            );

            $this->call(
                'intents:export',
                [
                    '--yes' => true
                ]
            );

            $this->call(
                'messages:export',
                [
                    '--yes' => true
                ]
            );
        }
    }
}
