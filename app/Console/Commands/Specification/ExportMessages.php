<?php

namespace App\Console\Commands\Specification;

use OpenDialogAi\ResponseEngine\MessageTemplate;

class ExportMessages extends BaseSpecificationCommand
{
    protected $signature = 'messages:export {message?} {--y|yes}';

    protected $description = 'Export all message templates';

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
                $this->exportMessageTemplate($messageTemplate);
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

        $output = "<intent>" . $messageTemplate->outgoingIntent->name . "</intent>\n";
        $output .= "<name>" . $messageTemplate->name . "</name>\n";

        if ($messageTemplate->conditions) {
            $output .= "<conditions>\n" . $messageTemplate->conditions . "\n</conditions>\n";
        }
        $output .= $messageTemplate->message_markup;

        $messageFileName = "$messageTemplate->name.message";
        $filename = $this->getMessagePath($messageFileName);
        file_put_contents($filename, $output);
    }
}
