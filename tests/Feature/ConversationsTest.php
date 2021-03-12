<?php

namespace Tests\Feature;

use App\User;
use OpenDialogAi\Core\Conversation\Exceptions\ConversationObjectNotFoundException;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use Tests\TestCase;

class ConversationsTest extends TestCase
{
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
    }

    public function testGetConversationsRequiresAuthentication()
    {
        $this->get('/admin/api/conversation-builder/conversations/trigger302')
            ->assertStatus(302);
    }

    public function testGetConversationNotFound()
    {
        ConversationDataClient::shouldReceive('getConversationByUid')
            ->once()
            ->with('test', false)
            ->andThrow(new ConversationObjectNotFoundException());

        $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/conversation-builder/conversations/test')
            ->assertStatus(404);
    }
}
