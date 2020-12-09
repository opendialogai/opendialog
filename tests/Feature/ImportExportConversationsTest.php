<?php

namespace Tests\Feature;

use App\ImportExportHelpers\ConversationImportExportHelper;
use Illuminate\Support\Facades\Artisan;
use OpenDialogAi\ConversationBuilder\Conversation;

/**
 * Class ImportExportConversationsTest
 * @package Tests\Feature
 * @group SpecificationTests
 */
class ImportExportConversationsTest extends BaseSpecificationTest
{
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

        $conversationFileName = "$conversation->name.conv";
        $filename = ConversationImportExportHelper::getConversationPath($conversationFileName);
        $model = $this->disk->get($filename);
        $this->assertStringContainsString('intent.core.NoMatchResponse2', $model);
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

        $conversationFileName = "$conversation->name.conv";
        $filename = ConversationImportExportHelper::getConversationPath($conversationFileName);
        $this->disk->put($filename, $model);

        Artisan::call(
            'conversations:import',
            [
                '--yes' => true,
                'conversation' => 'no_match_conversation'
            ]
        );

        $conversation = Conversation::where('name', 'no_match_conversation')->first();
        $this->assertStringContainsString('intent.core.NoMatchResponse2', $conversation->model);
    }
}
