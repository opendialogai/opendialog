<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Support\Str;
use OpenDialogAi\ConversationBuilder\Conversation;
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
}
