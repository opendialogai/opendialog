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
            $conversationsPath = $this->getConversationsPath();
            $conversationFiles = glob("$conversationsPath/*");
            foreach ($conversationFiles as $conversationFile) {
                if (is_file($conversationFile)) {
                    unlink($conversationFile);
                }
            }

            $intentsPath = $this->getIntentsPath();
            $intentFiles = glob("$intentsPath/*");
            foreach ($intentFiles as $intentFile) {
                if (is_file($intentFile)) {
                    unlink($intentFile);
                }
            }

            $messagesPath = $this->getMessagesPath();
            $messageFiles = glob("$messagesPath/*");
            foreach ($messageFiles as $messageFile) {
                if (is_file($messageFile)) {
                    unlink($messageFile);
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
