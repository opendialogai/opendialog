<?php

namespace Tests\Feature;

use App\User;
use OpenDialogAi\ConversationLog\ChatbotUser;
use Tests\TestCase;

class ChatbotUsersTest extends TestCase
{
    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();

        factory(ChatbotUser::class)->create();
        factory(ChatbotUser::class)->create();
        factory(ChatbotUser::class)->create();
    }

    public function testChatbotUsersViewEndpoint()
    {
        $chatboutUser = ChatbotUser::first();

        $this->get('/admin/api/chatbot-user/' . $chatboutUser->id)
            ->assertStatus(302);

        $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/chatbot-user/' . $chatboutUser->id)
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
            ->json('GET', '/admin/api/chatbot-user')
            ->assertStatus(200)
            ->assertJsonCount(count($chatboutUsers))
            ->assertJson([
                $chatboutUsers[0]->toArray(),
                $chatboutUsers[1]->toArray(),
                $chatboutUsers[2]->toArray(),
            ]);
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
            ->json('DELETE', '/admin/api/chatbot-user/' . $chatboutUser->id)
            ->assertStatus(405);
    }
}
