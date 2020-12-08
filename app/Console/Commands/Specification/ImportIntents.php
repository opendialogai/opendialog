<?php

namespace App\Console\Commands\Specification;

use OpenDialogAi\ResponseEngine\OutgoingIntent;

class ImportIntents extends BaseSpecificationCommand
{
    protected $signature = 'intents:import {outgoingIntent?} {--y|yes}';

    protected $description = 'Sets up all intents';

    public function handle()
    {
        $outgoingIntentName = $this->argument('outgoingIntent');

        if ($this->option('yes')) {
            $continue = true;
        } else {
            $continue = $this->confirm(
                'This will import or update all intents. Are you sure you want to continue?'
            );
        }

        if ($continue) {
            if ($outgoingIntentName) {
                $this->importOutgoingIntent($outgoingIntentName . '.intent');
            } else {
                $files = preg_grep('/^([^.])/', scandir($this->getIntentsPath()));

                foreach ($files as $messageName) {
                    $this->importOutgoingIntent($messageName);
                }
            }

            $this->info('Import of intents finished');
        } else {
            $this->info('Bye');
        }
    }

    protected function importOutgoingIntent($outgoingIntentFileName): void
    {
        $filename = $this->getIntentPath($outgoingIntentFileName);
        $data = file_get_contents($filename);

        $intentName = $this->getIntentNameFromIntentXml($data);

        $this->info(sprintf('Importing intent %s', $intentName));

        $newIntent = OutgoingIntent::firstOrNew(['name' => $intentName]);
        $newIntent->save();
    }
}
