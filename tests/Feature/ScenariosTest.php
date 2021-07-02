<?php


namespace Tests\Feature;

use App\Http\Facades\Serializer;
use App\Http\Resources\ScenarioResource;
use App\User;
use Carbon\Carbon;
use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\Core\Conversation\ConditionCollection;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\ConversationCollection;
use OpenDialogAi\Core\Conversation\Exceptions\ConversationObjectNotFoundException;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\IntentCollection;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\ScenarioCollection;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\SceneCollection;
use OpenDialogAi\Core\Conversation\Turn;
use OpenDialogAi\Core\Conversation\TurnCollection;
use Tests\TestCase;

class ScenariosTest extends TestCase
{
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
    }

    public function testGetScenariosRequiresAuthentication()
    {
        $this->get('/admin/api/conversation-builder/scenarios')
            ->assertStatus(302);
    }

    public function testGetScenarios()
    {
        $fakeScenario1 = new Scenario();
        $fakeScenario1->setName("Example scenario");
        $fakeScenario1->setUid('0x0001');
        $fakeScenario1->setODId('example_scenario');

        $fakeScenario2 = new Scenario();
        $fakeScenario2->setName("Example scenario");
        $fakeScenario2->setUid('0x0001');
        $fakeScenario2->setODId('example_scenario');

        $fakeScenarioCollection = new ScenarioCollection();
        $fakeScenarioCollection->addObject($fakeScenario1);
        $fakeScenarioCollection->addObject($fakeScenario2);

        ConversationDataClient::shouldReceive('getAllScenarios')
            ->once()
            ->andReturn($fakeScenarioCollection);

        Serializer::shouldReceive('normalize')
            ->once()
            ->with($fakeScenarioCollection, 'json', ScenarioResource::$fields)
            ->andReturn(json_decode('[
            {
            "uid": "0x0001",
            "odId": "example_scenario1",
            "name": "Example scenario1",
            "description": "An example scenario",
            "updatedAt": "2021-02-25T14:30:00.000Z",
            "createdAt": "2021-02-24T09:30:00.000Z",
            "defaultInterpreter": "interpreter.core.nlp",
            "behaviors": [],
            "conditions": [],
            "status": "PUBLISHED",
            "conversations": ["0x0002"]
        },
        {
            "uid": "0x0002",
            "odId": "example_scenario2",
            "name": "Example scenario2",
            "description": "An example scenario",
            "updatedAt": "2021-02-25T14:30:00.000Z",
            "createdAt": "2021-02-24T09:30:00.000Z",
            "defaultInterpreter": "interpreter.core.nlp",
            "behaviors": [],
            "conditions": [],
            "status": "PUBLISHED",
            "conversations": ["0x0002"]
        }]'));


        $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/conversation-builder/scenarios')
            ->assertStatus(200)
            ->assertJson([[
                "uid"=> "0x0001",
                "odId"=> "example_scenario1",
                "name"=> "Example scenario1",
                ],
                [
                "uid"=> "0x0002",
                "odId"=> "example_scenario2",
                "name"=> "Example scenario2"
                ]]);
    }

    public function testGetScenarioNotFound()
    {
        ConversationDataClient::shouldReceive('getScenarioByUid')
            ->once()
            ->with('test', false)
            ->andThrow(new ConversationObjectNotFoundException());

        $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/conversation-builder/scenarios/test')
            ->assertStatus(404);
    }

    public function testGetScenarioByUid()
    {
        $fakeScenario = $this->getFakeScenario();

        Serializer::shouldReceive('normalize')
            ->once()
            ->with($fakeScenario, 'json', ScenarioResource::$fields)
            ->andReturn(json_decode('{
            "uid": "0x0001",
            "odId": "example_scenario",
            "name": "Example scenario",
            "description": "An example scenario",
            "updatedAt": "2021-02-25T14:30:00.000Z",
            "createdAt": "2021-02-24T09:30:00.000Z",
            "defaultInterpreter": "interpreter.core.nlp",
            "behaviors": [],
            "conditions": [],
            "status": "PUBLISHED",
            "conversations": ["0x0002"]
        }', true));

        ConversationDataClient::shouldReceive('getScenarioByUid')
            ->once()
            ->with($fakeScenario->getUid(), false)
            ->andReturn($fakeScenario);

        $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/conversation-builder/scenarios/' . $fakeScenario->getUid())
            ->assertJson([
                'name' => 'Example scenario',
                'uid' => '0x0001',
                'odId' => 'example_scenario',
                'description' =>  'An example scenario'
            ]);
    }

    public function testCreateInvalidScenario()
    {
        $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/conversation-builder/scenarios/', [
                'status' => 'not valid',
            ])
            ->assertStatus(422);
    }

    public function testCreateNewScenario()
    {
        $fakeScenario = new Scenario();
        $fakeScenario->setName("Example scenario");
        $fakeScenario->setODId("example_scenario");
        $fakeScenario->setDescription('An example scenario');

        $fakeWelcomeConversation = new Conversation($fakeScenario);
        $fakeWelcomeConversation->setName('Welcome Conversation');
        $fakeWelcomeConversation->setOdId('welcome_conversation');
        $fakeWelcomeConversation->setDescription('Automatically generated');
        $fakeScenario->setConversations(new ConversationCollection([$fakeWelcomeConversation]));

        $fakeWelcomeScene = new Scene($fakeWelcomeConversation);
        $fakeWelcomeScene->setName('Welcome Scene');
        $fakeWelcomeScene->setOdId('welcome_scene');
        $fakeWelcomeScene->setDescription('Automatically generated');
        $fakeWelcomeConversation->setScenes(new SceneCollection([$fakeWelcomeScene]));

        $fakeWelcomeTurn = new Turn($fakeWelcomeScene);
        $fakeWelcomeTurn->setName('Welcome Turn');
        $fakeWelcomeTurn->setOdId('welcome_turn');
        $fakeWelcomeTurn->setDescription('Automatically generated');
        $fakeWelcomeScene->setTurns(new TurnCollection([$fakeWelcomeTurn]));

        $fakeWelcomeIntent = new Intent($fakeWelcomeTurn);
        $fakeWelcomeIntent->setName('Welcome Intent');
        $fakeWelcomeIntent->setOdId('intent.app.welcomeResponse');
        $fakeWelcomeIntent->setSpeaker(Intent::APP);
        $fakeWelcomeIntent->setDescription('Automatically generated');
        $fakeWelcomeIntent->setIsRequestIntent(true);
        $fakeWelcomeTurn->setRequestIntents(new IntentCollection([$fakeWelcomeIntent]));

        $fakeTriggerConversation = new Conversation($fakeScenario);
        $fakeTriggerConversation->setName('Trigger Conversation');
        $fakeTriggerConversation->setOdId('trigger_conversation');
        $fakeTriggerConversation->setDescription('Automatically generated');
        $fakeScenario->setConversations(new ConversationCollection([$fakeTriggerConversation]));

        $fakeTriggerScene = new Scene($fakeTriggerConversation);
        $fakeTriggerScene->setName('Trigger Scene');
        $fakeTriggerScene->setOdId('trigger_scene');
        $fakeTriggerScene->setDescription('Automatically generated');
        $fakeTriggerConversation->setScenes(new SceneCollection([$fakeTriggerScene]));

        $fakeTriggerTurn = new Turn($fakeTriggerScene);
        $fakeTriggerTurn->setName('Trigger Turn');
        $fakeTriggerTurn->setOdId('trigger_turn');
        $fakeTriggerTurn->setDescription('Automatically generated');
        $fakeTriggerScene->setTurns(new TurnCollection([$fakeTriggerTurn]));

        $fakeTriggerIntent = new Intent($fakeTriggerTurn);
        $fakeTriggerIntent->setName('Trigger Intent');
        $fakeTriggerIntent->setOdId('intent.core.welcome');
        $fakeTriggerIntent->setSpeaker(Intent::USER);
        $fakeTriggerIntent->setDescription('Automatically generated');
        $fakeTriggerIntent->setIsRequestIntent(true);
        $fakeTriggerTurn->setRequestIntents(new IntentCollection([$fakeTriggerIntent]));

        $fakeScenarioCreated = clone($fakeScenario);
        $fakeScenarioCreated->setUid("0x0001");

        $condition = new Condition(
            'eq',
            ['attribute' => 'user.selected_scenario'],
            ['value' => $fakeScenarioCreated->getUid()]
        );

        $fakeScenarioUpdated = clone($fakeScenarioCreated);
        $fakeScenarioUpdated->setConditions(new ConditionCollection([$condition]));

        $fakeWelcomeConversationCreated = clone($fakeWelcomeConversation);
        $fakeWelcomeConversationCreated->setUid("0x0001");

        Serializer::shouldReceive('deserialize')
            ->once()
            ->andReturn($fakeScenario);

        Serializer::shouldReceive('normalize')
            ->once()
            ->with($fakeScenarioUpdated, 'json', ScenarioResource::$fields)
            ->andReturn(json_decode('{
            "uid": "0x0001",
            "odId": "example_scenario",
            "name": "Example scenario",
            "description": "An example scenario",
            "conversations": [{"id": "0x0001"}]
        }', true));

        ConversationDataClient::shouldReceive('addFullScenarioGraph')
            ->once()
            ->with($fakeScenario)
            ->andReturn($fakeScenarioCreated);

        ConversationDataClient::shouldReceive('updateIntent')
            ->once()
            ->andReturn($fakeTriggerIntent);

        ConversationDataClient::shouldReceive('updateScenario')
            ->once()
            ->andReturn($fakeScenarioUpdated);

        $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/conversation-builder/scenarios/', [
                'name' => 'Example scenario',
                'odId' => 'example_scenario',
                'description' =>  'An example scenario'
            ])
            ->assertStatus(201)
            ->assertJson([
                'name' => 'Example scenario',
                'uid'=> '0x0001',
                'odId' => 'example_scenario',
                'description' =>  'An example scenario',
                'conversations' => [['id' => $fakeWelcomeConversationCreated->getUid()]]
            ]);
    }

    public function testDuplicateScenarioSuccess()
    {
        $scenario = $this->getFakeScenarioForDuplication();

        // Called during route binding
        ConversationDataClient::shouldReceive('getScenarioByUid')
            ->once()
            ->andReturn($scenario);

        // Called during OD ID rule
        ConversationDataClient::shouldReceive('getAllScenarios')
            ->once()
            ->andReturn(new ScenarioCollection([$scenario]));

        // Attempt to duplicate with same ID
        $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/conversation-builder/scenarios/' . $scenario->getUid() . '/duplicate', [
                'name' => $scenario->getName(),
                'od_id' => $scenario->getODId(),
            ])
            ->assertStatus(422);
    }

    public function testDuplicateScenarioFailure()
    {
        $scenario = $this->getFakeScenarioForDuplication();

        // Called during route binding
        ConversationDataClient::shouldReceive('getScenarioByUid')
            ->once()
            ->andReturn($scenario);

        $duplicated = null;
        ConversationDataClient::shouldReceive('addFullScenarioGraph')
            ->once()
            ->andReturnUsing(function ($scenario) use (&$duplicated) {
                $scenario = $scenario->copy();
                $scenario->setUid('0x9999');
                $duplicated = $scenario;
                return $scenario;
            });

        // Called in controller, once before persisting, again after, and finally after patching
        ConversationDataClient::shouldReceive('getFullScenarioGraph')
            ->times(3)
            ->andReturnUsing(
                fn ($uid) => $scenario,
                function ($uid) use (&$duplicated) {
                    return $duplicated;
                },
                function ($uid) use (&$duplicated) {
                    $duplicated->setConditions(new ConditionCollection([new Condition(
                        'eq',
                        ['attribute' => 'user.selected_scenario'],
                        ['value' => $uid]
                    )]));

                    return $duplicated;
                }
            );

        // Called in controller
        ConversationDataClient::shouldReceive('getAllScenarios')
            ->once()
            ->andReturn(new ScenarioCollection([$scenario]));

        // Called when patching the scenario's condition
        ConversationDataClient::shouldReceive('updateScenario')
            ->once()
            ->andReturnUsing(fn ($scenario) => $scenario);

        // Attempt to duplicate with different ID
        $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/conversation-builder/scenarios/' . $scenario->getUid() . '/duplicate')
            ->assertStatus(200)
            ->assertJson([
                'name' => 'Example scenario copy',
                'od_id' => 'example_scenario_copy',
                'id'=> '0x9999',
                "conditions" => [
                    [
                        "operation" => "eq",
                        "operationAttributes" => [
                            [
                                "id" => "attribute",
                                "value" => "user.selected_scenario"
                            ]
                        ],
                        "parameters" => [
                            [
                                "id" => "value",
                                "value" => "0x9999"
                            ]
                        ]
                    ]
                ]
            ]);
    }

    public function testUpdateScenarioNotFound()
    {
        ConversationDataClient::shouldReceive('getScenarioByUid')
            ->once()
            ->with('test', false)
            ->andThrow(new ConversationObjectNotFoundException());

        $this->actingAs($this->user, 'api')
            ->json('PUT', '/admin/api/conversation-builder/scenarios/test')
            ->assertStatus(404);
    }

    public function testUpdateScenario()
    {
        $fakeScenario = $this->getFakeScenario();
        ConversationDataClient::shouldReceive('getScenarioByUid')
            ->once()
            ->with($fakeScenario->getUid(), false)
            ->andReturn($fakeScenario);

        $fakeScenarioUpdated = new Scenario();
        $fakeScenarioUpdated->setName("Example scenario updated");
        $fakeScenarioUpdated->setUid("0x0001");
        $fakeScenarioUpdated->setODId("example_scenario");
        $fakeScenarioUpdated->setDescription('An example scenario updated');

        Serializer::shouldReceive('deserialize')
            ->once()
            ->andReturn($fakeScenarioUpdated);

        Serializer::shouldReceive('normalize')
            ->once()
            ->with($fakeScenarioUpdated, 'json', ScenarioResource::$fields)
            ->andReturn(json_decode('{
            "uid": "0x0001",
            "odId": "example_scenario",
            "name": "Example scenario updated",
            "description": "An example scenario updated"
        }', true));

        ConversationDataClient::shouldReceive('updateScenario')
            ->once()
            ->with($fakeScenarioUpdated)
            ->andReturn($fakeScenarioUpdated);

        $this->actingAs($this->user, 'api')
            ->json('PUT', '/admin/api/conversation-builder/scenarios/' . $fakeScenarioUpdated->getUid(), [
                'name' => $fakeScenarioUpdated->getName(),
                'uid' => $fakeScenarioUpdated->getUid(),
                'odId' => $fakeScenarioUpdated->getODId(),
                'description' =>  $fakeScenarioUpdated->getDescription()
            ])
            ->assertStatus(200)
            ->assertJson([
                'name' => 'Example scenario updated',
                'uid'=> '0x0001',
                'odId' => 'example_scenario',
                'description' =>  'An example scenario updated'
            ]);
    }

    public function deleteScenarioNotFound()
    {
        ConversationDataClient::shouldReceive('getScenarioByUid')
            ->once()
            ->with('test', false)
            ->andReturn(null);

        $this->actingAs($this->user, 'api')
            ->json('DELETE', '/admin/api/conversation-builder/scenarios/test')
            ->assertStatus(404);
    }

    public function testDeleteScenario()
    {
        $fakeScenario = $this->getFakeScenario();

        ConversationDataClient::shouldReceive('getScenarioByUid')
            ->once()
            ->with($fakeScenario->getUid(), false)
            ->andReturn($fakeScenario);

        ConversationDataClient::shouldReceive('deleteScenarioByUid')
            ->once()
            ->with($fakeScenario->getUid())
            ->andReturn(true);

        $this->actingAs($this->user, 'api')
            ->json('DELETE', '/admin/api/conversation-builder/scenarios/' . $fakeScenario->getUid())
            ->assertStatus(200);
    }

    /**
     * @return Scenario
     */
    public function getFakeScenario(): Scenario
    {
        $fakeScenario = new Scenario();
        $fakeScenario->setName("Example scenario");
        $fakeScenario->setUid('0x0001');
        $fakeScenario->setODId('example_scenario');
        $fakeScenario->setConditions(new ConditionCollection([
            new Condition(
                'eq',
                ['attribute' => 'user.selected_scenario'],
                ['value' => '0x0001'],
            )
        ]));
        $fakeScenario->setCreatedAt(Carbon::now());
        $fakeScenario->setUpdatedAt(Carbon::now());

        return $fakeScenario;
    }

    public function getFakeScenarioForDuplication(): Scenario
    {
        $scenario = $this->getFakeScenario();

        $conversations[] = new Conversation();
        $conversations[0]->setName("Example Conversation 1");
        $conversations[0]->setUid('0x0002');
        $conversations[0]->setOdId("example_conversation_1");
        $conversations[0]->setCreatedAt(Carbon::now());
        $conversations[0]->setUpdatedAt(Carbon::now());
        $conversations[0]->setScenario($scenario);

        $scenes[] = new Scene();
        $scenes[0]->setName("Example Scene 1");
        $scenes[0]->setUid('0x0003');
        $scenes[0]->setOdId("example_scene_1");
        $scenes[0]->setCreatedAt(Carbon::now());
        $scenes[0]->setUpdatedAt(Carbon::now());
        $scenes[0]->setConversation($conversations[0]);

        $turns[] = new Turn();
        $turns[0]->setName("Example Turn 1");
        $turns[0]->setUid('0x0004');
        $turns[0]->setOdId("example_turn_1");
        $turns[0]->setCreatedAt(Carbon::now());
        $turns[0]->setUpdatedAt(Carbon::now());
        $turns[0]->setScene($scenes[0]);

        $requestIntents[] = new Intent();
        $requestIntents[0]->setIsRequestIntent(true);
        $requestIntents[0]->setName("Example Intent 1");
        $requestIntents[0]->setUid('0x0005');
        $requestIntents[0]->setOdId("example_turn_1");
        $requestIntents[0]->setCreatedAt(Carbon::now());
        $requestIntents[0]->setUpdatedAt(Carbon::now());
        $requestIntents[0]->setTurn($turns[0]);

        $responseIntents[] = new Intent();
        $responseIntents[0]->setIsRequestIntent(true);
        $responseIntents[0]->setName("Example Intent 2");
        $responseIntents[0]->setUid('0x0006');
        $responseIntents[0]->setOdId("example_turn_2");
        $responseIntents[0]->setCreatedAt(Carbon::now());
        $responseIntents[0]->setUpdatedAt(Carbon::now());
        $responseIntents[0]->setTurn($turns[0]);

        $turns[0]->setRequestIntents(new IntentCollection($requestIntents));
        $turns[0]->setResponseIntents(new IntentCollection($responseIntents));
        $scenes[0]->setTurns(new TurnCollection($turns));
        $conversations[0]->setScenes(new SceneCollection($scenes));
        $scenario->setConversations(new ConversationCollection($conversations));

        return $scenario;
    }
}
