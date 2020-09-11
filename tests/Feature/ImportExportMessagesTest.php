<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ImportExportMessagesTest extends TestCase
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

    public function testSetUpMessages()
    {
        $this->assertDatabaseMissing('outgoing_intents', ['name' => 'intent.core.NoMatchResponse']);

        Artisan::call(
            'messages:setup',
            [
                '--yes' => true
            ]
        );

        $this->assertDatabaseHas('outgoing_intents', ['name' => 'intent.core.NoMatchResponse']);
    }

    public function testExportMessages()
    {
        Artisan::call(
            'messages:setup',
            [
                '--yes' => true
            ]
        );

        $this->assertDatabaseHas('outgoing_intents', ['name' => 'intent.core.NoMatchResponse']);

        Artisan::call(
            'messages:export',
            [
                '--yes' => true
            ]
        );
    }

    public function testUpdateMessages()
    {
        Artisan::call(
            'messages:setup',
            [
                '--yes' => true
            ]
        );

        $this->assertDatabaseHas('outgoing_intents', ['name' => 'intent.core.NoMatchResponse']);

        Artisan::call(
            'messages:update',
            [
                '--yes' => true
            ]
        );
    }
}
