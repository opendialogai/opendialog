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

        $xml = new \SimpleXMLElement("<parent></parent>");
        $xml->addChild('message-template');
        $xml->{'message-template'}->addChild('intent', $messageTemplate->outgoingIntent->name);
        $xml->{'message-template'}->addChild('name', $messageTemplate->name);

        if ($messageTemplate->conditions) {
            $xml->{'message-template'}->addChild('conditions', $messageTemplate->conditions);
        }

        $xml->{'message-template'}->addChild('markup');

        $data = $xml->{'message-template'}->asXML();
        $data = str_replace('<markup/>', sprintf('<markup>%s</markup>', $messageTemplate->message_markup), $data);

        $messageFileName = $this->addMessageFileExtension($messageTemplate->name);
        $this->createMessageFile($messageFileName, $data);
    }
}
