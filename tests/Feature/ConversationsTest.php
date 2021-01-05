<?php

namespace Tests\Feature;

use App\ImportExportHelpers\ConversationImportExportHelper;
use App\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use OpenDialogAi\ConversationBuilder\Conversation;
use OpenDialogAi\ConversationEngine\ConversationStore\DGraphConversationQueryFactory;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
use OpenDialogAi\ResponseEngine\MessageTemplate;
use OpenDialogAi\ResponseEngine\OutgoingIntent;
use Tests\TestCase;

class ConversationsTest extends TestCase
{
    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->initDDgraph();

        $this->user = factory(User::class)->create();

        for ($i = 0; $i < 52; $i++) {
            factory(Conversation::class)->create();
        }
    }

    public function testConversationsViewEndpoint()
    {
        $conversation = Conversation::first();

        $this->get('/admin/api/conversation/' . $conversation->id)
            ->assertStatus(302);

        $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/conversation/' . $conversation->id)
            ->assertStatus(200)
            ->assertJsonFragment(
                [
                    'name' => $conversation->name,
                    'model' => $conversation->model,
                    'scenes_validation_status' => 'validated',
                    'yaml_schema_validation_status' => 'validated',
                    'yaml_validation_status' => 'validated',
                    'model_validation_status' => 'validated',
                ]
            );
    }

    public function testConversationsViewAllEndpoint()
    {
        $conversations = Conversation::all();

        $this->get('/admin/api/conversation')
            ->assertStatus(302);

        $response = $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/conversation?page=1')
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    $conversations[0]->toArray(),
                    $conversations[1]->toArray(),
                    $conversations[2]->toArray(),
                ],
            ])
            ->getData();

        $this->assertEquals(count($response->data), 50);

        $response = $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/conversation?page=2')
            ->assertStatus(200)
            ->getData();

        $this->assertEquals(count($response->data), 2);
    }

    public function testConversationsUpdateEndpoint()
    {
        $conversation = Conversation::first();

        $this->actingAs($this->user, 'api')
            ->json('PATCH', '/admin/api/conversation/' . $conversation->id, [
                'name' => 'updated_name',
                'model' => 'conversation:
  id: updated_name
  scenes:
    opening_scene:
      intents:
        - u:
            i: intent.core.hello_bot
        - b:
            i: intent.core.hello_human
            completes: true',
            ])
            ->assertStatus(200);

        $updatedConversation = Conversation::first();

        $this->assertEquals($updatedConversation->name, 'updated_name');
    }

    public function testConversationsStoreEndpoint()
    {
        $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/conversation', [
                'name' => 'test_conversation',
                'model' => 'conversation:
  id: test_conversation
  scenes:
    opening_scene:
      intents:
        - u:
            i: intent.core.hello_bot
        - b:
            i: intent.core.hello_human
            completes: true',
            ])
            ->assertStatus(201)
            ->assertJsonFragment(
                [
                    'name' => 'test_conversation',
                    'model' => 'conversation:
  id: test_conversation
  scenes:
    opening_scene:
      intents:
        - u:
            i: intent.core.hello_bot
        - b:
            i: intent.core.hello_human
            completes: true',
                ]
            );
    }

    public function testConversationsDestroyEndpoint()
    {
        /** @var Conversation $firstConversation */
        $conversation = Conversation::first();
        $conversation->activateConversation();
        $conversation->deactivateConversation();
        $conversation->archiveConversation();

        $this->actingAs($this->user, 'api')
            ->json('DELETE', '/admin/api/conversation/' . $conversation->id)
            ->assertStatus(200);

        $this->assertEquals(Conversation::find($conversation->id), null);
    }

    public function testConversationsActivateEndpoint()
    {
        $conversation = Conversation::first();

        $response = $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/conversation/' . $conversation->id . '/activate')
            ->assertStatus(200);

        $this->assertEquals($response->content(), 'true');
    }

    public function testConversationsDeactivateEndpoint()
    {
        $conversation = Conversation::first();

        $conversation->activateConversation();

        $response = $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/conversation/' . $conversation->id . '/deactivate')
            ->assertStatus(200);

        $this->assertEquals($response->content(), 'true');
    }

    public function testConversationsArchiveEndpoint()
    {
        $conversation = Conversation::first();

        $conversation->activateConversation();
        $conversation->deactivateConversation();

        $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/conversation/' . $conversation->id . '/archive')
            ->assertStatus(200);
    }

    public function testConversationsMessageTemplatesEndpoint()
    {
        $conversation1 = Conversation::all()->get(1);
        $conversation2 = Conversation::all()->get(2);

        $intent1 = $conversation1->outgoing_intents[0]['name'];
        $intent2 = $conversation2->outgoing_intents[0]['name'];

        $outgoingIntent1 = OutgoingIntent::create([
            'name' => $intent1,
        ]);
        $outgoingIntent2 = OutgoingIntent::create([
            'name' => $intent2,
        ]);

        for ($j = 0; $j < 5; $j++) {
            $messageTemplate = factory(MessageTemplate::class)->make();
            $messageTemplate->outgoing_intent_id = $outgoingIntent1->id;
            $messageTemplate->save();
        }

        for ($j = 0; $j < 3; $j++) {
            $messageTemplate = factory(MessageTemplate::class)->make();
            $messageTemplate->outgoing_intent_id = $outgoingIntent2->id;
            $messageTemplate->save();
        }

        $response = $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/conversation/' . $conversation1->id . '/message-templates')
            ->assertStatus(200);
        $content = json_decode($response->content());
        $data = $content->data;

        $this->assertEquals(count($data), 5);
        foreach ($data as $message) {
            $this->assertEquals(!empty($message->id), true);
            $this->assertEquals(!empty($message->outgoing_intent), true);
            $this->assertEquals($message->outgoing_intent_id, $outgoingIntent1->id);
            $this->assertEquals($message->outgoing_intent->name, $intent1);
        }

        $response = $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/conversation/' . $conversation2->id . '/message-templates')
            ->assertStatus(200);
        $content = json_decode($response->content());
        $data = $content->data;

        $this->assertEquals(count($data), 3);
        foreach ($data as $message) {
            $this->assertEquals(!empty($message->id), true);
            $this->assertEquals(!empty($message->outgoing_intent), true);
            $this->assertEquals($message->outgoing_intent_id, $outgoingIntent2->id);
            $this->assertEquals($message->outgoing_intent->name, $intent2);
        }
    }

    public function testConversationsInvalidStoreEndpoint()
    {
        $response = $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/conversation', [
                'model' => 'conversation:
  id: test_conversation',
            ])
            ->assertStatus(400);

        $this->assertEquals($response->content(), '{"field":"model","message":"Conversation must have at least 1 scene."}');

        $response = $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/conversation', [
                'model' => 'conversation:
  id: ' . Str::random(1000) . '
  scenes:
    opening_scene:
      intents:
        - u:
            i: intent.core.hello_bot
        - b:
            i: intent.core.hello_human
            completes: true',
            ])
            ->assertStatus(400);

        $this->assertEquals($response->content(), '{"field":"name","message":"The maximum length for conversation id is 512."}');
    }

    public function testConversationImportEndpoint()
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
            ->post('/admin/api/conversations/import', [
                'activate' => true,
                'file1' => $conversationFile,
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
            ->post('/admin/api/conversations/import', [
                'activate' => true,
                'file1' => $conversationFile,
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
