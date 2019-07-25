<?php

namespace Tests\Feature;

use App\User;
use OpenDialogAi\ResponseEngine\MessageTemplate;
use OpenDialogAi\ResponseEngine\OutgoingIntent;
use Tests\TestCase;

class MessageTemplatesTest extends TestCase
{
    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();

        for ($i = 0; $i < 5; $i++) {
            $outgoingIntent = factory(OutgoingIntent::class)->create();

            for ($j = 0; $j < 52; $j++) {
                $messageTemplate = factory(MessageTemplate::class)->make();
                $messageTemplate->outgoing_intent_id = $outgoingIntent->id;
                $messageTemplate->save();
            }
        }
    }

    public function testMessageTemplatesViewEndpoint()
    {
        $messageTemplate = MessageTemplate::first();

        $this->get('/admin/api/outgoing-intents/' . $messageTemplate->outgoing_intent_id . '/message-templates/' . $messageTemplate->id)
            ->assertStatus(302);

        $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/outgoing-intents/' . $messageTemplate->outgoing_intent_id . '/message-templates/' . $messageTemplate->id)
            ->assertStatus(200)
            ->assertJsonFragment(
                [
                    'name' => $messageTemplate->name,
                    'conditions' => $messageTemplate->conditions,
                    'message_markup' => $messageTemplate->message_markup,
                ]
            );
    }

    public function testMessageTemplatesViewAllEndpoint()
    {
        $outgoingIntent = OutgoingIntent::first();

        $messageTemplates = MessageTemplate::where('outgoing_intent_id', $outgoingIntent->id)->get();

        $this->get('/admin/api/outgoing-intents/' . $outgoingIntent->id . '/message-templates')
            ->assertStatus(302);

        $response = $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/outgoing-intents/' . $outgoingIntent->id . '/message-templates?page=1')
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    $messageTemplates[0]->toArray(),
                    $messageTemplates[1]->toArray(),
                    $messageTemplates[2]->toArray(),
                ],
            ])
            ->getData();

        $this->assertEquals(count($response->data), 50);

        $response = $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/outgoing-intents/' . $outgoingIntent->id . '/message-templates?page=2')
            ->assertStatus(200)
            ->getData();

        $this->assertEquals(count($response->data), 2);
    }

    public function testMessageTemplatesUpdateEndpoint()
    {
        $messageTemplate = MessageTemplate::latest()->first();

        $this->actingAs($this->user, 'api')
            ->json('PATCH', '/admin/api/outgoing-intents/' . $messageTemplate->outgoing_intent_id . '/message-templates/' . $messageTemplate->id, [
                'name' => 'updated name',
            ])
            ->assertStatus(200);

        $updatedMessageTemplate = MessageTemplate::latest()->first();

        $this->assertEquals($updatedMessageTemplate->name, 'updated name');
    }

    public function testMessageTemplatesStoreEndpoint()
    {
        $outgoingIntent = OutgoingIntent::first();

        $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/outgoing-intents/' . $outgoingIntent->id . '/message-templates', [
                'name' => 'test',
                'conditions' => "conditions:\n- condition:\n    attribute: user.name\n    operation: eq\n    value: test",
                'message_markup' => '<message><text-message>Test</text-message></message>',
                'outgoing_intent_id' => $outgoingIntent->id,
            ])
            ->assertStatus(201)
            ->assertJsonFragment(
                [
                    'name' => 'test',
                    'conditions' => "conditions:\n- condition:\n    attribute: user.name\n    operation: eq\n    value: test",
                    'message_markup' => '<message><text-message>Test</text-message></message>',
                    'outgoing_intent_id' => $outgoingIntent->id,
                ]
            );
    }

    public function testMessageTemplatesDestroyEndpoint()
    {
        $messageTemplate = MessageTemplate::first();

        $this->actingAs($this->user, 'api')
            ->json('DELETE', '/admin/api/outgoing-intents/' . $messageTemplate->outgoing_intent_id . '/message-templates/' . $messageTemplate->id)
            ->assertStatus(200);

        $this->assertEquals(MessageTemplate::find($messageTemplate->id), null);
    }

    public function testMessageTemplatesInvalidStoreEndpoint()
    {
        $outgoingIntent = OutgoingIntent::first();
        $messageTemplate = MessageTemplate::first();

        $response = $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/outgoing-intents/' . $outgoingIntent->id . '/message-templates', [
                'name' => 'test',
                'conditions' => "conditions:\n- condition:\n    attribute: user.name\n    operation: eq\n    value: test",
                'message_markup' => '<message><text-message></text-message></message>',
                'outgoing_intent_id' => $outgoingIntent->id,
            ])
            ->assertStatus(400);

        $this->assertEquals($response->content(), '{"field":"message_markup","message":"Text messages must have \"text\"."}');

        $response = $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/outgoing-intents/' . $outgoingIntent->id . '/message-templates', [
                'name' => 'test 2',
                'conditions' => "conditions:\n- condition:\n    attribute: user.name\n    value: test",
                'message_markup' => '<message><text-message>Test</text-message></message>',
                'outgoing_intent_id' => $outgoingIntent->id,
            ])
            ->assertStatus(400);

        $this->assertEquals($response->content(), '{"field":"conditions","message":"Invalid condition found."}');

        $response = $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/outgoing-intents/' . $outgoingIntent->id . '/message-templates', [
                'name' => $messageTemplate->name,
                'conditions' => "conditions:\n- condition:\n    attribute: user.name\n    operation: eq\n    value: test",
                'message_markup' => '<message><text-message>Test</text-message></message>',
                'outgoing_intent_id' => $outgoingIntent->id,
            ])
            ->assertStatus(400);

        $this->assertEquals($response->content(), '{"field":"name","message":"Message template name is already in use."}');
    }

    public function testMessageTemplatesInvalidUpdateEndpoint()
    {
        $messageTemplate = MessageTemplate::latest()->first();

        $response = $this->actingAs($this->user, 'api')
            ->json('PATCH', '/admin/api/outgoing-intents/' . $messageTemplate->outgoing_intent_id . '/message-templates/' . $messageTemplate->id, [
                'message_markup' => '<message><text-message></text-message></message>',
            ])
            ->assertStatus(400);

        $this->assertEquals($response->content(), '{"field":"message_markup","message":"Text messages must have \"text\"."}');
    }
}
