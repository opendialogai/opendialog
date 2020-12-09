<?php

namespace Tests\Feature;

use App\Console\Commands\Specification\BaseSpecificationCommand;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Adapter\AbstractAdapter;
use League\Flysystem\Filesystem;
use OpenDialogAi\ResponseEngine\OutgoingIntent;
use Tests\TestCase;

/**
 * Class ImportExportIntentsTest
 * @package Tests\Feature
 * @group SpecificationTests
 */
class ImportExportIntentsTest extends TestCase
{
    /** @var Filesystem $disk */
    private $disk;

    public function setUp(): void
    {
        parent::setUp();

        Artisan::call(
            'schema:init',
            [
                '--yes' => true
            ]
        );

        $this->disk = Storage::fake('specifications');

        /** @var AbstractAdapter $diskAdapter */
        $diskAdapter = $this->disk->getAdapter();
        File::copyDirectory(resource_path('specifications'), $diskAdapter->getPathPrefix());
    }

    public function testImportIntents()
    {
        $this->assertDatabaseMissing('outgoing_intents', ['name' => 'intent.core.NoMatchResponse']);

        Artisan::call(
            'intents:import',
            [
                '--yes' => true
            ]
        );

        $this->assertDatabaseHas('outgoing_intents', ['name' => 'intent.core.NoMatchResponse']);
    }

    public function testExportIntents()
    {
        Artisan::call(
            'intents:import',
            [
                '--yes' => true
            ]
        );

        $this->assertDatabaseHas('outgoing_intents', ['name' => 'intent.core.NoMatchResponse']);

        $outgoingIntent = OutgoingIntent::where('name', 'intent.core.NoMatchResponse')->first();
        $outgoingIntent->name = $outgoingIntent->name . 'Export';
        $outgoingIntent->save();

        Artisan::call(
            'intents:export',
            [
                '--yes' => true
            ]
        );

        $intentFileName = "intent.core.NoMatchResponseExport.intent";
        $this->disk->assertExists(BaseSpecificationCommand::getIntentPath($intentFileName));

        $filename = BaseSpecificationCommand::getIntentPath($intentFileName);
        $intent = $this->disk->get($filename);
        $this->assertStringContainsString('<intent>intent.core.NoMatchResponseExport</intent>', $intent);
    }

    public function testUpdateIntents()
    {
        Artisan::call(
            'intents:import',
            [
                '--yes' => true
            ]
        );

        $this->assertDatabaseHas('outgoing_intents', ['name' => 'intent.core.NoMatchResponse']);

        $filename = BaseSpecificationCommand::getIntentPath("intent.core.NoMatchResponse.intent.xml");

        $intent = $this->disk->get($filename);
        $xml = new \SimpleXMLElement(sprintf("<parent>%s</parent>", $intent));
        $xml->intent->name = ((string) $xml->intent->name) . "Export";

        $newIntent = $xml->intent->asXML();
        $this->disk->put($filename, $newIntent);

        Artisan::call(
            'intents:import',
            [
                '--yes' => true
            ]
        );

        $outgoingIntent = OutgoingIntent::where('name', 'intent.core.NoMatchResponseExport')->first();
        $this->assertEquals('intent.core.NoMatchResponseExport', $outgoingIntent->name);
    }
}
