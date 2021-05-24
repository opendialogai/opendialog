<?php

namespace Tests\Feature;

use App\User;
use Carbon\Carbon;
use OpenDialogAi\Core\Conversation\BehaviorsCollection;
use OpenDialogAi\Core\Conversation\ConditionCollection;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\ConversationCollection;
use OpenDialogAi\Core\Conversation\Exceptions\ConversationObjectNotFoundException;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\Scenario;
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

    public function testGetAllConversationsByScenario()
    {
        $fakeScenario = new Scenario();
        $fakeScenario->setUid('0x0001');

        $fakeConversation = new Conversation();
        $fakeConversation->setUid('0x0002');
        $fakeConversation->setName('New Example conversation 1');
        $fakeConversation->setOdId('new_example_conversation_1');
        $fakeConversation->setDescription("An new example conversation 1");
        $fakeConversation->setInterpreter('interpreter.core.nlp');
        $fakeConversation->setBehaviors(new BehaviorsCollection());
        $fakeConversation->setConditions(new ConditionCollection());
        $fakeConversation->setCreatedAt(Carbon::parse('2021-03-12T11:57:23+0000'));
        $fakeConversation->setUpdatedAt(Carbon::parse('2021-03-12T11:57:23+0000'));

        $fakeConversation1 = new Conversation();
        $fakeConversation1->setUid('0x0003');
        $fakeConversation1->setName('New Example conversation 2');
        $fakeConversation1->setOdId('new_example_conversation_2');
        $fakeConversation1->setDescription("An new example conversation 2");
        $fakeConversation1->setInterpreter('interpreter.core.nlp');
        $fakeConversation1->setBehaviors(new BehaviorsCollection());
        $fakeConversation1->setConditions(new ConditionCollection());
        $fakeConversation1->setCreatedAt(Carbon::parse('2021-03-12T11:57:23+0000'));
        $fakeConversation1->setUpdatedAt(Carbon::parse('2021-03-12T11:57:23+0000'));

        $fakeConversationCollection = new ConversationCollection();
        $fakeConversationCollection->addObject($fakeConversation);
        $fakeConversationCollection->addObject($fakeConversation1);



        ConversationDataClient::shouldReceive('getScenarioByUid')
            ->once()
            ->with($fakeScenario->getUid(), false)
            ->andReturn($fakeScenario);

        ConversationDataClient::shouldReceive('getAllConversationsByScenario')
            ->once()
            ->andReturn($fakeConversationCollection);

        $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/conversation-builder/scenarios/' . $fakeScenario->getUid() . '/conversations')
            ->assertStatus(200)
            ->assertJson([[
                "id" => "0x0002",
                "od_id"=> "new_example_conversation_1",
                "name"=> "New Example conversation 1",
                "description"=> "An new example conversation 1"
            ], [
                "id"=> "0x0003",
                "od_id"=> "new_example_conversation_2",
                "name"=> "New Example conversation 2",
                "description"=> "An new example conversation 2"
            ]]);
    }

    public function testAddConversationToScenario()
    {
        $fakeScenario = new Scenario();
        $fakeScenario->setUid('0x0001');
        $fakeScenario->setName("Example scenario");
        $fakeScenario->setOdId('example_scenario');
        $fakeScenario->setDescription('An example scenario');

        $fakeConversation = new Conversation();
        $fakeConversation->setUid('0x0002');
        $fakeConversation->setName('New Example conversation');
        $fakeConversation->setOdId('new_example_conversation');
        $fakeConversation->setDescription("An new example conversation");
        $fakeConversation->setInterpreter('interpreter.core.nlp');
        $fakeConversation->setBehaviors(new BehaviorsCollection());
        $fakeConversation->setConditions(new ConditionCollection());
        $fakeConversation->setCreatedAt(Carbon::parse('2021-03-12T11:57:23+0000'));
        $fakeConversation->setUpdatedAt(Carbon::parse('2021-03-12T11:57:23+0000'));


        ConversationDataClient::shouldReceive('addConversation')
            ->once()
            ->withAnyArgs()
            ->andReturn($fakeConversation);

        ConversationDataClient::shouldReceive('getScenarioByUid')
            ->once()
            ->with($fakeScenario->getUid(), false)
            ->andReturn($fakeScenario);

        $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/conversation-builder/scenarios/' . $fakeScenario->getUid() . '/conversations', [
                "od_id" => "new_example_conversation",
                "name" => "New Example conversaation",
                "description" =>  "An new example conversation",
                "default_interpreter" =>  "interpreter.core.nlp",
                "conditions" =>  [],
                "behaviors" =>  []
            ])
            ->assertStatus(200)
            ->assertJson([
                "id"=> "0x0002",
                "od_id"=> "new_example_conversation",
                "name"=> "New Example conversation",
                "description"=> "An new example conversation",
                "interpreter"=> "interpreter.core.nlp",
                "created_at"=> "2021-03-12T11:57:23+0000",
                "updated_at"=> "2021-03-12T11:57:23+0000",
                "conditions"=> [],
                "behaviors"=> []
            ]);
    }

    public function testGetConversationByUid()
    {
        $fakeConversation = new Conversation();
        $fakeConversation->setUid('0x0001');
        $fakeConversation->setOdId('new_example_conversation');
        $fakeConversation->setName('New Example conversation');
        $fakeConversation->setDescription('An new example conversation');
        $fakeConversation->setInterpreter('interpreter.core.nlp');
        $fakeConversation->setBehaviors(new BehaviorsCollection());
        $fakeConversation->setConditions(new ConditionCollection());
        $fakeConversation->setCreatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $fakeConversation->setUpdatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));

        ConversationDataClient::shouldReceive('getConversationByUid')
            ->once()
            ->with($fakeConversation->getUid(), false)
            ->andReturn($fakeConversation);

        $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/conversation-builder/conversations/' . $fakeConversation->getUid())
            ->assertExactJson([
                "id" => "0x0001",
                "name" => "New Example conversation",
                "od_id" => "new_example_conversation",
                "description" => "An new example conversation",
                "interpreter" => "interpreter.core.nlp",
                "behaviors" => [],
                "conditions" => [],
                "scenes" => [],
                "created_at" => "2021-02-24T09:30:00+0000",
                "updated_at" => "2021-02-24T09:30:00+0000"
            ]);
    }

    public function testUpdateConversationByUid()
    {
        $fakeConversation = new Conversation();
        $fakeConversation->setUid('0x0001');
        $fakeConversation->setOdId('new_example_conversation');
        $fakeConversation->setName('New Example conversation');
        $fakeConversation->setDescription('An new example conversation');
        $fakeConversation->setInterpreter('interpreter.core.nlp');
        $fakeConversation->setBehaviors(new BehaviorsCollection());
        $fakeConversation->setConditions(new ConditionCollection());
        $fakeConversation->setCreatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $fakeConversation->setUpdatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));

        $fakeConversationUpdated = new Conversation();
        $fakeConversationUpdated->setUid('0x0001');
        $fakeConversationUpdated->setOdId('new_example_conversation');
        $fakeConversationUpdated->setName('New Example conversation updated');
        $fakeConversationUpdated->setDescription('An new example conversation updated');
        $fakeConversationUpdated->setInterpreter('interpreter.core.nlp');
        $fakeConversationUpdated->setBehaviors(new BehaviorsCollection());
        $fakeConversationUpdated->setConditions(new ConditionCollection());
        $fakeConversationUpdated->setCreatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $fakeConversationUpdated->setUpdatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));


        ConversationDataClient::shouldReceive('getConversationByUid')
            ->once()
            ->with($fakeConversation->getUid(), false)
            ->andReturn($fakeConversation);

        ConversationDataClient::shouldReceive('updateConversation')
            ->once()
            ->withAnyArgs()
            ->andReturn($fakeConversationUpdated);

        $this->actingAs($this->user, 'api')
            ->json('PATCH', '/admin/api/conversation-builder/conversations/' . $fakeConversation->getUid(), [
                'name' => $fakeConversationUpdated->getName(),
                'id' => $fakeConversationUpdated->getUid(),
                'description' =>  $fakeConversationUpdated->getDescription()
            ])
            //->assertStatus(200)
            ->assertJson([
                "id" => "0x0001",
                "name" => "New Example conversation updated",
                "description" => "An new example conversation updated",
                "interpreter" => "interpreter.core.nlp",
                "created_at" => "2021-02-24T09:30:00+0000",
                "updated_at" => "2021-02-24T09:30:00+0000",
                "conditions" => [],
                "behaviors" => []
            ]);
    }

    public function testDeleteConversationByUid()
    {
        $fakeConversation = new Conversation();
        $fakeConversation->setUid('0x0001');
        $fakeConversation->setOdId('new_example_conversation');
        $fakeConversation->setName('New Example conversation');
        $fakeConversation->setDescription('An new example conversation');

        ConversationDataClient::shouldReceive('getConversationByUid')
            ->once()
            ->with($fakeConversation->getUid(), false)
            ->andReturn($fakeConversation);

        ConversationDataClient::shouldReceive('deleteConversationByUid')
            ->once()
            ->with($fakeConversation->getUid())
            ->andReturn(true);

        $this->actingAs($this->user, 'api')
            ->json('DELETE', '/admin/api/conversation-builder/conversations/' . $fakeConversation->getUid())
            ->assertStatus(200);
    }
}
