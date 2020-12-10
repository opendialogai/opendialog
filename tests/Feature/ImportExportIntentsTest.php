<?php

namespace Tests\Feature;

use App\ImportExportHelpers\IntentImportExportHelper;
use Illuminate\Support\Facades\Artisan;
use OpenDialogAi\ResponseEngine\OutgoingIntent;

/**
 * Class ImportExportIntentsTest
 * @package Tests\Feature
 * @group SpecificationTests
 */
class ImportExportIntentsTest extends BaseSpecificationTest
{
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

        $welcomeOriginalIntentFileName = IntentImportExportHelper::addIntentFileExtension("intent.opendialog.WelcomeResponse");
        $welcomeIntentFileName = IntentImportExportHelper::addIntentFileExtension("intent.opendialog.WelcomeResponseExport");
        $noMatchOriginalIntentFileName = IntentImportExportHelper::addIntentFileExtension("intent.core.NoMatchResponse");
        $noMatchIntentFileName = IntentImportExportHelper::addIntentFileExtension("intent.core.NoMatchResponseExport");

        // This command is not destructive so the original should remain as well
        $this->disk->assertExists(IntentImportExportHelper::getIntentPath($welcomeOriginalIntentFileName));
        $this->disk->assertExists(IntentImportExportHelper::getIntentPath($welcomeIntentFileName));
        $this->disk->assertExists(IntentImportExportHelper::getIntentPath($noMatchOriginalIntentFileName));
        $this->disk->assertExists(IntentImportExportHelper::getIntentPath($noMatchIntentFileName));

        $welcomeFilePath = IntentImportExportHelper::getIntentPath($welcomeIntentFileName);
        $welcomeIntent = $this->disk->get($welcomeFilePath);
        $noMatchFilePath = IntentImportExportHelper::getIntentPath($noMatchIntentFileName);
        $noMatchIntent = $this->disk->get($noMatchFilePath);
        $this->assertXmlStringEqualsXmlString('<intent><name>intent.opendialog.WelcomeResponseExport</name></intent>', $welcomeIntent);
        $this->assertXmlStringEqualsXmlString('<intent><name>intent.core.NoMatchResponseExport</name></intent>', $noMatchIntent);
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

        $welcomeOriginalIntentFileName = IntentImportExportHelper::addIntentFileExtension("intent.opendialog.WelcomeResponse");
        $welcomeIntentFileName = IntentImportExportHelper::addIntentFileExtension("intent.opendialog.WelcomeResponseExport");
        $noMatchOriginalIntentFileName = IntentImportExportHelper::addIntentFileExtension("intent.core.NoMatchResponse");
        $noMatchIntentFileName = IntentImportExportHelper::addIntentFileExtension("intent.core.NoMatchResponseExport");

        // This command is not destructive so the original should remain as well
        $this->disk->assertExists(IntentImportExportHelper::getIntentPath($welcomeOriginalIntentFileName));
        $this->disk->assertExists(IntentImportExportHelper::getIntentPath($noMatchOriginalIntentFileName));
        $this->disk->assertExists(IntentImportExportHelper::getIntentPath($noMatchIntentFileName));

        // We didn't export the new welcome intent so it should be missing
        $this->disk->assertMissing(IntentImportExportHelper::getIntentPath($welcomeIntentFileName));

        $welcomeFilePath = IntentImportExportHelper::getIntentPath($welcomeOriginalIntentFileName);
        $welcomeIntent = $this->disk->get($welcomeFilePath);
        $noMatchFilePath = IntentImportExportHelper::getIntentPath($noMatchIntentFileName);
        $noMatchIntent = $this->disk->get($noMatchFilePath);
        $this->assertXmlStringNotEqualsXmlString('<intent><name>intent.opendialog.WelcomeResponseExport</name></intent>', $welcomeIntent);
        $this->assertXmlStringEqualsXmlString('<intent><name>intent.core.NoMatchResponseExport</name></intent>', $noMatchIntent);
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

        $filename = IntentImportExportHelper::getIntentPath("intent.core.NoMatchResponse.intent.xml");

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
