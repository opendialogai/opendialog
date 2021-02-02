<?php

namespace App\Console\Commands\Specification;

use App\Http\Controllers\API\DynamicAttributesController;
use App\Http\Resources\DynamicAttributeCollection;
use App\ImportExportHelpers\DynamicAttributeImportExportHelper;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use OpenDialogAi\AttributeEngine\AttributeResolver\AttributeResolver;
use OpenDialogAi\AttributeEngine\AttributeTypeService\AttributeTypeServiceInterface;
use OpenDialogAi\AttributeEngine\DynamicAttribute;


class ImportDynamicAttributes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dynamic-attributes:import {name=custom-attributes} {--delete-existing} {--y|yes}';

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
     * @param  AttributeResolver              $attributeResolver
     * @param  AttributeTypeServiceInterface  $attributeTypeService
     *
     * @return mixed
     */
    public function handle(AttributeResolver  $attributeResolver, AttributeTypeServiceInterface $attributeTypeService)
    {
        $name = $this->argument('name');
        $filePath = DynamicAttributeImportExportHelper::getFilePath($name);

        $continue = $this->option('yes') ?
            true :
            $this->confirm(
                sprintf('Do you want to import all dynamic attributes from %s (%s) ?', $name, $filePath)
            );

        if ($continue) {
            $deleteExisting = $this->option('delete-existing');
            $this->info('Importing dynamic attributes...');

            if ($collection = $this->importDynamicAttributes($name, $attributeResolver, $attributeTypeService)) {
                try {
                    DB::transaction(function () use ($collection, $deleteExisting) {
                        if ($deleteExisting) {
                            $this->info("Deleting existing dynamic attributes...");
                            DynamicAttribute::truncate();
                        }
                        foreach ($collection as $attribute) {
                            $attribute->save();
                        }
                    });
                    $this->info('Import of dynamic attributes finished.');
                } catch (QueryException $e) {
                    $this->error("Unexpected error occurred saving dynamic attributes.");
                }
            } else {
                $this->error("Failed to create collection. Bailing...");
                return;
            }
        }
    }


    /**
     * @param  string                         $name
     * @param  AttributeResolver              $attributeResolver
     * @param  AttributeTypeServiceInterface  $attributeTypeService
     *
     * @return DynamicAttributeCollection
     */
    protected function importDynamicAttributes(
        string $name,
        AttributeResolver $attributeResolver,
        AttributeTypeServiceInterface $attributeTypeService
    ): ?DynamicAttributeCollection {
        $filePath = DynamicAttributeImportExportHelper::getFilePath($name);
        try {
            $data = DynamicAttributeImportExportHelper::getFileData($filePath);
            $dict = DynamicAttributeImportExportHelper::importFromString($data);
            if ($error = DynamicAttributesController::validateImport($dict, $attributeResolver,
                $attributeTypeService)) {
                $this->error(json_encode($error, JSON_PRETTY_PRINT));
                return null;
            }
            return DynamicAttributeCollection::fromDictionary($dict);
        } catch (FileNotFoundException $exception) {
            $this->error(sprintf('Could not find dynamic attributes file at at %s', $filePath));
            return null;
        } catch (\JsonException $e) {
            $this->error($e->getMessage());
            return null;
        }
    }
}
