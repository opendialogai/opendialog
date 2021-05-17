<?php


namespace Tests\Feature;

use App\User;
use Carbon\Carbon;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Exceptions\ConversationObjectNotFoundException;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\Facades\MessageTemplateDataClient;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\MessageTemplate;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\Turn;
use Tests\TestCase;

class MessageGraphTemplateTest extends TestCase
{
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
    }

    public function testGetScenesRequiresAuthentication()
    {
        $this->get('/admin/api/conversation-builder/intents/id/message-templates/id')
            ->assertStatus(302);
    }

    public function testGetIntentNotFound()
    {
        ConversationDataClient::shouldReceive('getIntentByUid')
            ->once()
            ->with('test', false)
            ->andThrow(new ConversationObjectNotFoundException());

        $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/conversation-builder/intents/test/message-templates/id')
            ->assertStatus(404);
    }

    public function testGetMessageTemplate()
    {
        $scenario = new Scenario();
        $scenario->setUid('0x00001');

        $conversation = new Conversation($scenario);
        $conversation->setUid('0x00002');

        $scene = new Scene($conversation);
        $scene->setUid('0x00003');

        $turn = new Turn($scene);
        $turn->setUid('0x00004');

        $intent = $this->createIntent($turn, '0x00005', 'test_id', Intent::USER);
        ConversationDataClient::shouldReceive('getIntentByUid')
            ->once()
            ->with('0x00001', false)
            ->andReturn($intent);

        $messageTemplate = new MessageTemplate();
        $messageTemplate->setUid('0x00006');
        $messageTemplate->setName('message Template');
        $messageTemplate->setMessageMarkUp('xml');
        $messageTemplate->setCreatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $messageTemplate->setUpdatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $messageTemplate->setIntent($intent);

        MessageTemplateDataClient::shouldReceive('getMessageTemplateById')
            ->once()
            ->with('0x00002')
            ->andReturn($messageTemplate);

        $response = $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/conversation-builder/intents/0x00001/message-templates/0x00002')
            ->assertStatus(200)
            ->assertJsonFragment([
                'id' => $messageTemplate->getUid(),
                'name' => 'message Template',
                'message_markup' => 'xml',
                ])
            ->getContent();

        $responseArray = json_decode($response, true);
        $this->assertArrayHasKey('intent', $responseArray);
        $responseIntent = $responseArray['intent'];
        $this->assertEquals($intent->getUid(), $responseIntent['id']);

        $this->assertArrayHasKey('turn', $responseIntent);
        $responseTurn = $responseIntent['turn'];
        $this->assertEquals($turn->getUid(), $responseTurn['id']);

        $this->assertArrayHasKey('scene', $responseTurn);
        $responseScene = $responseTurn['scene'];
        $this->assertEquals($scene->getUid(), $responseScene['id']);

        $this->assertArrayHasKey('conversation', $responseScene);
        $responseConversation = $responseScene['conversation'];
        $this->assertEquals($conversation->getUid(), $responseConversation['id']);

        $this->assertArrayHasKey('scenario', $responseConversation);
        $responseScenario = $responseConversation['scenario'];
        $this->assertEquals($scenario->getUid(), $responseScenario['id']);
    }

    public function testCreateIntent()
    {
        $intent = $this->createIntent(new Turn(), '0x00001', 'test_id', Intent::USER);
        ConversationDataClient::shouldReceive('getIntentByUid')
            ->once()
            ->with('0x00001', false)
            ->andReturn($intent);

        $messageTemplate = new MessageTemplate();
        $messageTemplate->setOdId('od_id');
        $messageTemplate->setName('name');
        $messageTemplate->setMessageMarkup('<message></message>');

        $createdMessageTemplate = clone($messageTemplate);
        $createdMessageTemplate->setUid('0x00002');
        $createdMessageTemplate->setCreatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $createdMessageTemplate->setUpdatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));

        MessageTemplateDataClient::shouldReceive('addMessageTemplateToIntent')
            ->once()
            ->withAnyArgs()
            ->andReturn($createdMessageTemplate);


        $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/conversation-builder/intents/0x00001/message-templates', [
                'od_id' => 'od_id',
                'name' => 'name',
                'message_markup' => '<message></message>'
            ])
            ->assertStatus(200)
            ->assertJsonFragment([
               'id' => $createdMessageTemplate->getUid(),
               'od_id' => 'od_id',
               'name' => 'name',
                'message_markup' => '<message></message>'
            ]);
    }

    public function testMessageValidation()
    {
        $intent = $this->createIntent(new Turn(), '0x00001', 'test_id', Intent::USER);
        ConversationDataClient::shouldReceive('getIntentByUid')
            ->times(4)
            ->with('0x00001', false)
            ->andReturn($intent);

        MessageTemplateDataClient::shouldReceive('addMessageTemplateToIntent')
            ->never();

        $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/conversation-builder/intents/0x00001/message-templates', [
                'message_markup' => '<message>njlsdfkjds</message>'
            ])->assertStatus(422);

        $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/conversation-builder/intents/0x00001/message-templates', [
                'message_markup' => '<message><madeup>this is a made up message type</madeup></message>'
            ])->assertStatus(422);

        $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/conversation-builder/intents/0x00001/message-templates', [
                'message_markup' => '<message><text-message/></message>'
            ])->assertStatus(422);

        // post without any message mark up should fail
        $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/conversation-builder/intents/0x00001/message-templates', [])
            ->assertStatus(422);
    }
}
