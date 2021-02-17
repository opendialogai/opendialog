<?php

namespace Tests\Feature;

use App\User;
use OpenDialogAi\ConversationBuilder\Conversation;
use OpenDialogAi\ConversationLog\ChatbotUser;
use OpenDialogAi\Core\RequestLog;
use OpenDialogAi\ResponseEngine\MessageTemplate;
use OpenDialogAi\ResponseEngine\OutgoingIntent;
use Tests\TestCase;

class StatisticsTest extends TestCase
{
    protected $user;

    public function setup(): void
    {
        $this->markTestSkipped();

        parent::setUp();

        $this->user = factory(User::class)->create();
    }

    public function testChatbotUsersEndpoint()
    {
        $startDate = new \DateTime();
        $startDate->modify('-1 day');

        $endDate = new \DateTime();
        $endDate->modify('+1 day');

        $response = $this->actingAs($this->user)
            ->get('/stats/chatbot-users?startdate=' . $startDate->format('Y-m-d') . '&enddate=' . $endDate->format('Y-m-d'))
            ->assertStatus(200)
            ->getData();

        $this->assertEquals($response->labels[0], $startDate->format('Y-m-d'));
        $this->assertEquals($response->labels[2], $endDate->format('Y-m-d'));
        $this->assertEquals($response->values[0], 0);
        $this->assertEquals($response->values[1], 0);
        $this->assertEquals($response->values[2], 0);
        $this->assertEquals($response->total, 0);

        for ($i = 0; $i < 5; $i++) {
            factory(ChatbotUser::class)->create();
        }

        $response = $this->actingAs($this->user)
            ->get('/stats/chatbot-users?startdate=' . $startDate->format('Y-m-d') . '&enddate=' . $endDate->format('Y-m-d'))
            ->assertStatus(200)
            ->getData();

        $this->assertEquals($response->labels[0], $startDate->format('Y-m-d'));
        $this->assertEquals($response->labels[2], $endDate->format('Y-m-d'));
        $this->assertEquals($response->values[0], 0);
        $this->assertEquals($response->values[1], 5);
        $this->assertEquals($response->values[2], 0);
        $this->assertEquals($response->total, 5);
    }

    public function testRequestsEndpoint()
    {
        $startDate = new \DateTime();
        $startDate->modify('-1 day');

        $endDate = new \DateTime();
        $endDate->modify('+1 day');

        $response = $this->actingAs($this->user)
            ->get('/stats/requests?startdate=' . $startDate->format('Y-m-d') . '&enddate=' . $endDate->format('Y-m-d'))
            ->assertStatus(200)
            ->getData();

        $this->assertEquals($response->labels[0], $startDate->format('Y-m-d'));
        $this->assertEquals($response->labels[2], $endDate->format('Y-m-d'));
        $this->assertEquals($response->values[0], 0);
        $this->assertEquals($response->values[1], 0);
        $this->assertEquals($response->values[2], 0);
        $this->assertEquals($response->total, 0);

        for ($i = 0; $i < 5; $i++) {
            factory(RequestLog::class)->create();
        }

        $response = $this->actingAs($this->user)
            ->get('/stats/requests?startdate=' . $startDate->format('Y-m-d') . '&enddate=' . $endDate->format('Y-m-d'))
            ->assertStatus(200)
            ->getData();

        $this->assertEquals($response->labels[0], $startDate->format('Y-m-d'));
        $this->assertEquals($response->labels[2], $endDate->format('Y-m-d'));
        $this->assertEquals($response->values[0], 0);
        $this->assertEquals($response->values[1], 5);
        $this->assertEquals($response->values[2], 0);
        $this->assertEquals($response->total, 5);
    }

    public function testConversationsEndpoint()
    {
        $response = $this->actingAs($this->user)
            ->get('/stats/conversations')
            ->assertStatus(200)
            ->getData();

        $this->assertEquals($response->value, 0);

        for ($i = 0; $i < 2; $i++) {
            $conversation = factory(Conversation::class)->create();
            $conversation->activateConversation();
        }

        $response = $this->actingAs($this->user)
            ->get('/stats/conversations')
            ->assertStatus(200)
            ->getData();

        $this->assertEquals($response->value, 2);
    }

    public function testIncomingIntentsEndpoint()
    {
        $response = $this->actingAs($this->user)
            ->get('/stats/incoming-intents')
            ->assertStatus(200)
            ->getData();

        $this->assertEquals($response->value, 0);

        for ($i = 0; $i < 2; $i++) {
            $conversation = factory(Conversation::class)->create();
            $conversation->activateConversation();
        }

        $response = $this->actingAs($this->user)
            ->get('/stats/incoming-intents')
            ->assertStatus(200)
            ->getData();

        $this->assertEquals($response->value, 2);
    }

    public function testMessageTemplatesEndpoint()
    {
        $response = $this->actingAs($this->user)
            ->get('/stats/message-templates')
            ->assertStatus(200)
            ->getData();

        $this->assertEquals($response->value, 0);

        for ($i = 0; $i < 5; $i++) {
            $outgoingIntent = factory(OutgoingIntent::class)->create();

            $messageTemplate = factory(MessageTemplate::class)->make();
            $messageTemplate->outgoing_intent_id = $outgoingIntent->id;
            $messageTemplate->save();
        }

        $response = $this->actingAs($this->user)
            ->get('/stats/message-templates')
            ->assertStatus(200)
            ->getData();

        $this->assertEquals($response->value, 5);
    }
}
