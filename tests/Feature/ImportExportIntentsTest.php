<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use OpenDialogAi\ResponseEngine\OutgoingIntent;
use Tests\TestCase;

class ImportExportIntentsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Artisan::call(
            'schema:init',
            [
                '--yes' => true
            ]
        );
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

        $filename = base_path('resources/intents/intent.core.NoMatchResponseExport.intent');
        $intent = file_get_contents($filename);
        $this->assertStringContainsString('<intent>intent.core.NoMatchResponseExport</intent>', $intent);

        $outgoingIntent = OutgoingIntent::where('name', 'intent.core.NoMatchResponseExport')->first();
        $outgoingIntent->name = 'intent.core.NoMatchResponse';
        $outgoingIntent->save();

        Artisan::call(
            'intents:export',
            [
                '--yes' => true
            ]
        );

        unlink($filename);
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

        $filename = base_path('resources/intents/intent.core.NoMatchResponse.intent');

        $intent = file_get_contents($filename);
        $intent = str_replace('</intent>', 'Export</intent>', $intent);
        file_put_contents($filename, $intent);

        Artisan::call(
            'intents:import',
            [
                '--yes' => true
            ]
        );

        $outgoingIntent = OutgoingIntent::where('name', 'intent.core.NoMatchResponseExport')->first();
        $this->assertEquals('intent.core.NoMatchResponseExport', $outgoingIntent->name);

        $intent = str_replace('Export</intent>', '</intent>', $intent);
        file_put_contents($filename, $intent);
    }
}
