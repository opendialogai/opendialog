<?php

namespace App\Console\Commands\Specification;

use App\Http\Resources\DynamicAttributeCollection;
use App\ImportExportHelpers\DynamicAttributeImportExportHelper;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use OpenDialogAi\AttributeEngine\DynamicAttribute;

class ExportDynamicAttributes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'custom-attributes:export {name=custom-attributes} {--overwrite} {--y|yes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exports all dynamic attributes to file with given name.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $name = $this->argument('name');

        $continue = $this->option('yes') ? true : $this->confirm('Do you want to export all custom attributes?');

        if ($continue) {
            $overwrite = $this->option('overwrite');
            if (DynamicAttributeImportExportHelper::exists($name)) {
                if ($overwrite === true) {
                    $continue = true;
                }
                if ($overwrite === false) {
                    $this->info('A custom-attributes file already exists. Use the --overwrite flag to overwrite it.');
                    $continue = false;
                }
                if ($overwrite === null) {
                    $continue = $this->confirm('A custom-attributes file already exists. Do you want to overwrite it?');
                }
            }
        }

        if ($continue) {
            $this->exportDynamicAttributes(DynamicAttribute::all(), $name);
            $this->info('Export of custom attributes finished.');
        } else {
            $this->info('Bye!');
        }
    }


    /**
     * Exports a collection of dynamic attributes to a file.
     *
     * @param Collection $collection
     * @param string $name
     */
    protected function exportDynamicAttributes(Collection $collection, string $name)
    {
        $filePath = DynamicAttributeImportExportHelper::getFilePath($name);
        $this->info(sprintf("Exporting custom attributes to $filePath ..."));

        DynamicAttributeImportExportHelper::overwrite(DynamicAttributeCollection::toDictionary($collection), $filePath);
    }


}
