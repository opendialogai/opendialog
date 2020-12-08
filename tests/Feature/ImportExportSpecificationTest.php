<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

/**
 * Class ImportExportSpecificationTest
 * @package Tests\Feature
 * @group SpecificationTests
 */
class ImportExportSpecificationTest extends TestCase
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

    public function testImportSpecification()
    {
        $this->assertDatabaseMissing('conversations', ['name' => 'no_match_conversation']);
        $this->assertDatabaseMissing('outgoing_intents', ['name' => 'intent.core.NoMatchResponse']);
        $this->assertDatabaseMissing('message_templates', ['name' => 'Did not understand']);

        Artisan::call(
            'specification:import',
            [
                '--yes' => true
            ]
        );

        $this->assertDatabaseHas('conversations', ['name' => 'no_match_conversation']);
        $this->assertDatabaseHas('outgoing_intents', ['name' => 'intent.core.NoMatchResponse']);
        $this->assertDatabaseHas('message_templates', ['name' => 'Did not understand']);
    }

    public function testExportSpecification()
    {
        $this->assertDatabaseMissing('conversations', ['name' => 'no_match_conversation']);
        $this->assertDatabaseMissing('outgoing_intents', ['name' => 'intent.core.NoMatchResponse']);
        $this->assertDatabaseMissing('message_templates', ['name' => 'Did not understand']);

        Artisan::call(
            'specification:import',
            [
                '--yes' => true
            ]
        );

        $this->assertDatabaseHas('conversations', ['name' => 'no_match_conversation']);
        $this->assertDatabaseHas('outgoing_intents', ['name' => 'intent.core.NoMatchResponse']);
        $this->assertDatabaseHas('message_templates', ['name' => 'Did not understand']);

        Artisan::call(
            'specification:export',
            [
                '--yes' => true
            ]
        );
    }
}
