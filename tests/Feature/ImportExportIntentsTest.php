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
        $this->assertDatabaseMissing('outgoing_intents', ['name' => 'intent.opendialog.WelcomeResponse']);
        $this->assertDatabaseMissing('outgoing_intents', ['name' => 'intent.core.NoMatchResponse']);

        Artisan::call(
            'intents:import',
            [
                '--yes' => true
            ]
        );

        $this->assertDatabaseHas('outgoing_intents', ['name' => 'intent.opendialog.WelcomeResponse']);
        $this->assertDatabaseHas('outgoing_intents', ['name' => 'intent.core.NoMatchResponse']);
    }

    public function testImportSingleIntent()
    {
        $this->assertDatabaseMissing('outgoing_intents', ['name' => 'intent.opendialog.WelcomeResponse']);
        $this->assertDatabaseMissing('outgoing_intents', ['name' => 'intent.core.NoMatchResponse']);

        Artisan::call(
            'intents:import',
            [
                'outgoingIntent' => 'intent.core.NoMatchResponse',
                '--yes' => true
            ]
        );

        $this->assertDatabaseMissing('outgoing_intents', ['name' => 'intent.opendialog.WelcomeResponse']);
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

        $this->assertDatabaseHas('outgoing_intents', ['name' => 'intent.opendialog.WelcomeResponse']);
        $this->assertDatabaseHas('outgoing_intents', ['name' => 'intent.core.NoMatchResponse']);

        $welcomeOutgoingIntent = OutgoingIntent::where('name', 'intent.opendialog.WelcomeResponse')->first();
        $welcomeOutgoingIntent->name = $welcomeOutgoingIntent->name . 'Export';
        $welcomeOutgoingIntent->save();

        $noMatchOutgoingIntent = OutgoingIntent::where('name', 'intent.core.NoMatchResponse')->first();
        $noMatchOutgoingIntent->name = $noMatchOutgoingIntent->name . 'Export';
        $noMatchOutgoingIntent->save();

        Artisan::call(
            'intents:export',
            [
                '--yes' => true
            ]
        );

        $welcomeOriginalIntentFileName = BaseSpecificationCommand::addIntentFileExtension("intent.opendialog.WelcomeResponse");
        $welcomeIntentFileName = BaseSpecificationCommand::addIntentFileExtension("intent.opendialog.WelcomeResponseExport");
        $noMatchOriginalIntentFileName = BaseSpecificationCommand::addIntentFileExtension("intent.core.NoMatchResponse");
        $noMatchIntentFileName = BaseSpecificationCommand::addIntentFileExtension("intent.core.NoMatchResponseExport");

        // This command is not destructive so the original should remain as well
        $this->disk->assertExists(BaseSpecificationCommand::getIntentPath($welcomeOriginalIntentFileName));
        $this->disk->assertExists(BaseSpecificationCommand::getIntentPath($welcomeIntentFileName));
        $this->disk->assertExists(BaseSpecificationCommand::getIntentPath($noMatchOriginalIntentFileName));
        $this->disk->assertExists(BaseSpecificationCommand::getIntentPath($noMatchIntentFileName));

        $welcomeFilePath = BaseSpecificationCommand::getIntentPath($welcomeIntentFileName);
        $welcomeIntent = $this->disk->get($welcomeFilePath);
        $noMatchFilePath = BaseSpecificationCommand::getIntentPath($noMatchIntentFileName);
        $noMatchIntent = $this->disk->get($noMatchFilePath);
        $this->assertStringContainsString('<intent>intent.opendialog.WelcomeResponseExport</intent>', $welcomeIntent);
        $this->assertStringContainsString('<intent>intent.core.NoMatchResponseExport</intent>', $noMatchIntent);
    }

    public function testExportSingleIntent()
    {
        Artisan::call(
            'intents:import',
            [
                '--yes' => true
            ]
        );

        $this->assertDatabaseHas('outgoing_intents', ['name' => 'intent.opendialog.WelcomeResponse']);
        $this->assertDatabaseHas('outgoing_intents', ['name' => 'intent.core.NoMatchResponse']);

        $welcomeOutgoingIntent = OutgoingIntent::where('name', 'intent.opendialog.WelcomeResponse')->first();
        $welcomeOutgoingIntent->name = $welcomeOutgoingIntent->name . 'Export';
        $welcomeOutgoingIntent->save();

        $noMatchOutgoingIntent = OutgoingIntent::where('name', 'intent.core.NoMatchResponse')->first();
        $noMatchOutgoingIntent->name = $noMatchOutgoingIntent->name . 'Export';
        $noMatchOutgoingIntent->save();

        Artisan::call(
            'intents:export',
            [
                'outgoingIntent' => 'intent.core.NoMatchResponseExport',
                '--yes' => true
            ]
        );

        $welcomeOriginalIntentFileName = BaseSpecificationCommand::addIntentFileExtension("intent.opendialog.WelcomeResponse");
        $welcomeIntentFileName = BaseSpecificationCommand::addIntentFileExtension("intent.opendialog.WelcomeResponseExport");
        $noMatchOriginalIntentFileName = BaseSpecificationCommand::addIntentFileExtension("intent.core.NoMatchResponse");
        $noMatchIntentFileName = BaseSpecificationCommand::addIntentFileExtension("intent.core.NoMatchResponseExport");

        // This command is not destructive so the original should remain as well
        $this->disk->assertExists(BaseSpecificationCommand::getIntentPath($welcomeOriginalIntentFileName));
        $this->disk->assertExists(BaseSpecificationCommand::getIntentPath($noMatchOriginalIntentFileName));
        $this->disk->assertExists(BaseSpecificationCommand::getIntentPath($noMatchIntentFileName));

        // We didn't export the new welcome intent so it should be missing
        $this->disk->assertMissing(BaseSpecificationCommand::getIntentPath($welcomeIntentFileName));

        $welcomeFilePath = BaseSpecificationCommand::getIntentPath($welcomeOriginalIntentFileName);
        $welcomeIntent = $this->disk->get($welcomeFilePath);
        $noMatchFilePath = BaseSpecificationCommand::getIntentPath($noMatchIntentFileName);
        $noMatchIntent = $this->disk->get($noMatchFilePath);
        $this->assertStringNotContainsString('<intent>intent.opendialog.WelcomeResponseExport.intent</intent>', $welcomeIntent);
        $this->assertStringContainsString('<intent>intent.core.NoMatchResponseExport</intent>', $noMatchIntent);
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
