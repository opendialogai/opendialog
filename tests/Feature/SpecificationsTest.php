<?php

namespace Tests\Feature;

use App\ImportExportHelpers\ConversationImportExportHelper;
use App\User;
use Illuminate\Http\UploadedFile;
use OpenDialogAi\ConversationEngine\ConversationStore\DGraphConversationQueryFactory;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
use Tests\TestCase;

class SpecificationsTest extends TestCase
{
    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->initDDgraph();

        $this->user = factory(User::class)->create();
    }

    public function testSpecificationImportEndpoint()
    {
        $this->assertDatabaseMissing('conversations', ['name' => 'no_match_conversation']);

        $this->assertEmpty(
            resolve(DGraphClient::class)
                ->query(DGraphConversationQueryFactory::getAllOpeningIntents())
                ->getData()
        );

        $conversationFileName = ConversationImportExportHelper::addConversationFileExtension('no_match_conversation');
        $conversationFile = UploadedFile::fake()->createWithContent(
            $conversationFileName,
            ConversationImportExportHelper::getConversationFileData(
                ConversationImportExportHelper::getConversationPath($conversationFileName)
            )
        );

        $this->actingAs($this->user, 'api')
            ->post('/admin/api/specification-import', [
                'file1' => $conversationFile
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('conversations', ['name' => 'no_match_conversation']);

        $this->assertCount(
            1,
            resolve(DGraphClient::class)
                ->query(DGraphConversationQueryFactory::getAllOpeningIntents())
                ->getData()
        );

        // Ensure re-importing works as expected (eg. that existing conversation activation is handled correctly)
        $this->actingAs($this->user, 'api')
            ->post('/admin/api/specification-import', [
                'file1' => $conversationFile
            ])
            ->assertStatus(200);

        $this->assertCount(
            1,
            resolve(DGraphClient::class)
                ->query(DGraphConversationQueryFactory::getAllOpeningIntents())
                ->getData()
        );
    }
}
