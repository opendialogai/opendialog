<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ImportExportConversationsTest extends TestCase
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

    public function testSetUpConversations()
    {
        $this->assertDatabaseMissing('conversations', ['name' => 'no_match_conversation']);

        Artisan::call(
            'conversations:setup',
            [
                '--yes' => true
            ]
        );

        $this->assertDatabaseHas('conversations', ['name' => 'no_match_conversation']);
    }

    public function testExportConversations()
    {
        Artisan::call(
            'conversations:setup',
            [
                '--yes' => true
            ]
        );

        $this->assertDatabaseHas('conversations', ['name' => 'no_match_conversation']);

        Artisan::call(
            'conversations:export',
            [
                '--yes' => true
            ]
        );
    }

    public function testUpdateConversations()
    {
        Artisan::call(
            'conversations:setup',
            [
                '--yes' => true
            ]
        );

        $this->assertDatabaseHas('conversations', ['name' => 'no_match_conversation']);

        Artisan::call(
            'conversations:update',
            [
                '--yes' => true,
                'conversation' => 'no_match_conversation'
            ]
        );
    }
}
