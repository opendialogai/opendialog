<?php

namespace App\Console\Commands\Specification;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
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
                $files = $this->getIntentFiles();

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
        try {
            $data = $this->getIntentFileData($outgoingIntentFileName);
        } catch (FileNotFoundException $e) {
            $this->warn(sprintf('Could not find intent at %s', $outgoingIntentFileName));
            return;
        }

        $xml = new \SimpleXMLElement($data);
        $intentName = (string) $xml->name;

        $this->info(sprintf('Importing intent %s', $intentName));

        $newIntent = OutgoingIntent::firstOrNew(['name' => $intentName]);
        $newIntent->save();
    }
}
