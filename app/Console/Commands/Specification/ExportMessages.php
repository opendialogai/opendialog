<?php

namespace App\Console\Commands\Specification;

use App\ImportExportHelpers\Generator\MessageFileGenerator;
use App\ImportExportHelpers\MessageImportExportHelper;
use Illuminate\Console\Command;
use OpenDialogAi\ResponseEngine\MessageTemplate;

class ExportMessages extends Command
{
    protected $signature = 'messages:export {message?} {--y|yes}';

    protected $description = 'Exports all message templates.';

    public function handle()
    {
        $messageName = $this->argument('message');

        if ($this->option('yes')) {
            $continue = true;
        } elseif ($messageName) {
            $continue = $this->confirm(
                sprintf(
                    'Do you want to export message %s?',
                    $messageName
                )
            );
        } else {
            $continue = $this->confirm('Do you want to export all messages?');
        }

        if ($continue) {
            if ($messageName) {
                $messageTemplate = MessageTemplate::where('name', $messageName)->first();

                if (is_null($messageTemplate)) {
                    $this->error(sprintf('%s doesn\'t exist.', $messageName));
                    return;
                } else {
                    $this->exportMessageTemplate($messageTemplate);
                }
            } else {
                $messageTemplates = MessageTemplate::all();

                foreach ($messageTemplates as $messageTemplate) {
                    $this->exportMessageTemplate($messageTemplate);
                }
            }

            $this->info('Export of messages finished');
        } else {
            $this->info('Bye');
        }
    }

    protected function exportMessageTemplate(MessageTemplate $messageTemplate): void
    {
        $this->info(sprintf('Exporting message %s', $messageTemplate->name));

        $messageFile = new MessageFileGenerator(
            $messageTemplate->outgoingIntent->name,
            $messageTemplate->name,
            $messageTemplate->message_markup
        );

        if ($messageTemplate->conditions) {
            $messageFile->setConditions($messageTemplate->conditions);
        }

        $messageFileName = MessageImportExportHelper::addMessageFileExtension($messageTemplate->name);
        MessageImportExportHelper::createMessageFile($messageFileName, $messageFile);
    }
}
