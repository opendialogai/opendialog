<?php

namespace App\Console\Commands\Specification;

use App\Http\Controllers\API\DynamicAttributesController;
use App\Http\Resources\DynamicAttributeCollection;
use App\ImportExportHelpers\DynamicAttributeImportExportHelper;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\DB;
use OpenDialogAi\AttributeEngine\DynamicAttribute;


class ImportDynamicAttributes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'custom-attributes:import {name=custom-attributes} {--delete-existing} {--y|yes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports all dynamic attributes from the file with the given name.';

    /**
     * Create a new command instance.
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     *
     * @return mixed
     */
    public function handle()
    {
        $name = $this->argument('name');
        $filePath = DynamicAttributeImportExportHelper::getFilePath($name);

        $confirmationMessage = sprintf('Do you want to import all dynamic attributes from %s (%s) ?', $name, $filePath);
        $continue = $this->option('yes') ?: $this->confirm($confirmationMessage);

        if ($continue) {
            $deleteExisting = $this->option('delete-existing');

            DB::beginTransaction();
            try {
                $this->info('Importing custom attributes...');
                if ($deleteExisting) {
                    $this->info("Deleting existing custom attributes...");
                    DynamicAttribute::truncate();
                }
                if ($collection = $this->importDynamicAttributes($name)) {
                    foreach ($collection as $attribute) {
                        $attribute->save();
                    }
                    $this->info('Import of custom attributes finished.');
                    DB::commit();
                } else {
                    $this->error("Failed to create collection. Restoring database...");
                    DB::rollBack();
                }
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Unexpected error occurred saving custom attributes.");
            }
        }
    }


    /**
     * @param string  $name
     *
     * @return DynamicAttributeCollection
     */
    protected function importDynamicAttributes(
        string $name
    ): ?DynamicAttributeCollection {
        $filePath = DynamicAttributeImportExportHelper::getFilePath($name);
        try {
            $data = DynamicAttributeImportExportHelper::getFileData($filePath);
            $dict = DynamicAttributeImportExportHelper::importFromString($data);
            if ($error = DynamicAttributesController::validateImport($dict)) {
                $this->error($this->formatError($error));
                return null;
            }
            return DynamicAttributeCollection::fromDictionary($dict);
        } catch (FileNotFoundException $exception) {
            $this->error(sprintf('Could not find custom attributes file at at %s', $filePath));
            return null;
        } catch (\JsonException $e) {
            $this->error($e->getMessage());
            return null;
        }
    }


    /**
     * @param  array  $error
     *
     * @return string
     */
    protected function formatError(array $error): string
    {
        $message = $error['message'];

        if ($ids = $error['ids'] ?? null) {
            $bullets = implode("\n", array_map(fn($item) => "* $item", $ids));
            $message .= "\nIDs:\n".$bullets;
        }

        if ($types = $error['types'] ?? null) {
            $bullets = implode("\n", array_map(fn($item) => "* $item", $types));
            $message .= "\nTypes:\n".$bullets;
        }

        return $message;
    }
}
