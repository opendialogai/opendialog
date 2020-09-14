<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ExportSpecification extends Command
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
            $conversationfiles = preg_grep('/^([^.])/', scandir(base_path('resources/conversations')));

            $conversationFiles = glob(base_path('resources/conversations/*'));
            foreach ($conversationFiles as $conversationFile) {
                if (is_file($conversationFile)) {
                    unlink($conversationFile);
                }
            }

            $intentFiles = glob(base_path('resources/intents/*'));
            foreach ($intentFiles as $intentFile) {
                if (is_file($intentFile)) {
                    unlink($intentFile);
                }
            }

            $messageFiles = glob(base_path('resources/messages/*'));
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
