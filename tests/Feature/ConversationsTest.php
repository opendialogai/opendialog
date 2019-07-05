<?php

namespace Tests\Feature;

use App\User;
use OpenDialogAi\ConversationBuilder\Conversation;
use Tests\TestCase;

class ConversationsTest extends TestCase
{
    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();

        factory(Conversation::class)->create();
        factory(Conversation::class)->create();
        factory(Conversation::class)->create();
    }

    public function testConversationsViewEndpoint()
    {
        $conversation = Conversation::first();

        $this->get('/admin/api/conversation/' . $conversation->id)
            ->assertStatus(302);

        $this->actingAs($this->user)
            ->json('GET', '/admin/api/conversation/' . $conversation->id)
            ->assertStatus(200)
            ->assertJsonFragment(
                [
                    'name' => $conversation->name,
                    'model' => $conversation->model,
                    'scenes_validation_status' => 'invalid',
                    'yaml_schema_validation_status' => 'invalid',
                    'yaml_validation_status' => 'validated',
                    'model_validation_status' => 'invalid',
                ]
            );
    }

    public function testConversationsViewAllEndpoint()
    {
        $conversations = Conversation::all();

        $this->get('/admin/api/conversation')
            ->assertStatus(302);

        $response = $this->actingAs($this->user)
            ->json('GET', '/admin/api/conversation')
            ->assertStatus(200)
            ->assertJsonCount(count($conversations))
            ->assertJson([
                $conversations[0]->toArray(),
                $conversations[1]->toArray(),
                $conversations[2]->toArray(),
            ]);
    }

    public function testConversationsUpdateEndpoint()
    {
        $conversation = Conversation::first();

        $this->actingAs($this->user)
            ->json('PATCH', '/admin/api/conversation/' . $conversation->id, [
                'name' => 'updated name',
            ])
            ->assertStatus(200);

        $updatedConversation = Conversation::first();

        $this->assertEquals($updatedConversation->name, 'updated name');
    }

    public function testConversationsStoreEndpoint()
    {
        $this->actingAs($this->user)
            ->json('POST', '/admin/api/conversation', [
                'name' => 'test_conversation',
                'model' => 'conversation:
  id: test_conversation',
            ])
            ->assertStatus(201)
            ->assertJsonFragment(
                [
                    'name' => 'test_conversation',
                    'model' => 'conversation:
  id: test_conversation',
                ]
            );
    }

    public function testConversationsDestroyEndpoint()
    {
        $conversation = Conversation::first();

        $this->actingAs($this->user)
            ->json('DELETE', '/admin/api/conversation/' . $conversation->id)
            ->assertStatus(200);

        $this->assertEquals(Conversation::find($conversation->id), null);
    }
}
