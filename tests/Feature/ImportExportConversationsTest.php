<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use OpenDialogAi\ConversationBuilder\Conversation;
use Tests\TestCase;

/**
 * Class ImportExportConversationsTest
 * @package Tests\Feature
 * @group SpecificationTests
 */
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

    public function testImportConversations()
    {
        $this->assertDatabaseMissing('conversations', ['name' => 'no_match_conversation']);

        Artisan::call(
            'conversations:import',
            [
                '--yes' => true
            ]
        );

        $this->assertDatabaseHas('conversations', ['name' => 'no_match_conversation']);
    }

    public function testExportConversations()
    {
        Artisan::call(
            'conversations:import',
            [
                '--yes' => true
            ]
        );

        $this->assertDatabaseHas('conversations', ['name' => 'no_match_conversation']);

        $conversation = Conversation::where('name', 'no_match_conversation')->first();
        $conversation->model = str_replace('intent.core.NoMatchResponse', 'intent.core.NoMatchResponse2', $conversation->model);
        $conversation->save();

        Artisan::call(
            'conversations:export',
            [
                '--yes' => true
            ]
        );

        $filename = base_path("resources/conversations/$conversation->name.conv");
        $model = file_get_contents($filename);
        $this->assertStringContainsString('intent.core.NoMatchResponse2', $model);

        $conversation = Conversation::where('name', 'no_match_conversation')->first();
        $conversation->model = str_replace('intent.core.NoMatchResponse2', 'intent.core.NoMatchResponse', $conversation->model);
        $conversation->save();

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
            'conversations:import',
            [
                '--yes' => true
            ]
        );

        $this->assertDatabaseHas('conversations', ['name' => 'no_match_conversation']);

        $conversation = Conversation::where('name', 'no_match_conversation')->first();
        $model = str_replace('intent.core.NoMatchResponse', 'intent.core.NoMatchResponse2', $conversation->model);

        $filename = base_path("resources/conversations/$conversation->name.conv");
        file_put_contents($filename, $model);

        Artisan::call(
            'conversations:import',
            [
                '--yes' => true,
                'conversation' => 'no_match_conversation'
            ]
        );

        $conversation = Conversation::where('name', 'no_match_conversation')->first();
        $this->assertStringContainsString('intent.core.NoMatchResponse2', $conversation->model);

        $model = str_replace('intent.core.NoMatchResponse2', 'intent.core.NoMatchResponse', $model);
        file_put_contents($filename, $model);
    }
}
