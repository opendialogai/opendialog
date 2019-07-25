<?php

namespace Tests\Feature;

use App\User;
use OpenDialogAi\ConversationLog\ChatbotUser;
use OpenDialogAi\ConversationLog\Message;
use Tests\TestCase;

class ChatbotUsersTest extends TestCase
{
    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();

        for ($i = 0; $i < 52; $i++) {
            factory(ChatbotUser::class)->create();
        }
    }

    public function testChatbotUsersViewEndpoint()
    {
        $chatboutUser = ChatbotUser::first();

        $this->get('/admin/api/chatbot-user/' . $chatboutUser->user_id)
            ->assertStatus(302);

        $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/chatbot-user/' . $chatboutUser->user_id)
            ->assertStatus(200)
            ->assertJsonFragment(
                [
                    'user_id' => $chatboutUser->user_id,
                    'first_name' => $chatboutUser->first_name,
                    'last_name' => $chatboutUser->last_name,
                    'email' => $chatboutUser->email,
                ]
            );
    }

    public function testChatbotUsersViewAllEndpoint()
    {
        $chatboutUsers = ChatbotUser::all();

        $this->get('/admin/api/chatbot-user')
            ->assertStatus(302);

        $response = $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/chatbot-user?page=1')
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    $chatboutUsers[0]->toArray(),
                    $chatboutUsers[1]->toArray(),
                    $chatboutUsers[2]->toArray(),
                ],
            ])
            ->getData();

        $this->assertEquals(count($response->data), 50);

        $response = $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/chatbot-user?page=2')
            ->assertStatus(200)
            ->getData();

        $this->assertEquals(count($response->data), 2);
    }

    public function testChatbotUsersUpdateEndpoint()
    {
        $chatboutUser = ChatbotUser::first();

        $this->actingAs($this->user, 'api')
            ->json('PATCH', '/admin/api/chatbot-user', [
                'user_id' => 'test',
            ])
            ->assertStatus(405);
    }

    public function testChatbotUsersStoreEndpoint()
    {
        $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/chatbot-user', [
                'user_id' => 'test',
            ])
            ->assertStatus(405);
    }

    public function testChatbotUsersDestroyEndpoint()
    {
        $chatboutUser = ChatbotUser::first();

        $this->actingAs($this->user, 'api')
            ->json('DELETE', '/admin/api/chatbot-user/' . $chatboutUser->user_id)
            ->assertStatus(405);
    }

    public function testChatbotUsersMessagesEndpoint()
    {
        $chatboutUser = ChatbotUser::first();

        for ($i = 0; $i < 10; $i++) {
            $message = factory(Message::class)->make();
            $message->user_id = $chatboutUser->user_id;
            $message->save();
        }

        for ($i = 0; $i < 5; $i++) {
            $message = factory(Message::class)->make();
            $message->save();
        }

        $response = $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/chatbot-user/' . $chatboutUser->user_id . '/messages')
            ->assertStatus(200)
            ->getData();

        $this->assertEquals(count($response->data), 10);
    }
}
