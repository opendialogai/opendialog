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
use OpenDialogAi\Core\Conversation\Facades\ScenarioDataClient;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\IntentCollection;
use OpenDialogAi\Core\Conversation\MessageTemplate;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\ScenarioCollection;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\SceneCollection;
use OpenDialogAi\Core\Conversation\Turn;
use OpenDialogAi\Core\Conversation\TurnCollection;
use OpenDialogAi\MessageBuilder\MessageMarkUpGenerator;
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
        $fakeScenario = self::getFakeScenario();

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

        $fakeTriggerWelcomeIntent = new Intent($fakeTriggerTurn);
        $fakeTriggerWelcomeIntent->setName('Trigger Intent');
        $fakeTriggerWelcomeIntent->setOdId('intent.core.welcome');
        $fakeTriggerWelcomeIntent->setSpeaker(Intent::USER);
        $fakeTriggerWelcomeIntent->setDescription('Automatically generated');
        $fakeTriggerWelcomeIntent->setIsRequestIntent(true);
        $fakeTriggerTurn->setRequestIntents(new IntentCollection([$fakeTriggerWelcomeIntent]));

        $fakeTriggerRestartIntent = new Intent($fakeTriggerTurn);
        $fakeTriggerRestartIntent->setName('Trigger Intent');
        $fakeTriggerRestartIntent->setOdId('intent.core.restart');
        $fakeTriggerRestartIntent->setSpeaker(Intent::USER);
        $fakeTriggerRestartIntent->setDescription('Automatically generated');
        $fakeTriggerRestartIntent->setIsRequestIntent(true);
        $fakeTriggerTurn->setRequestIntents(new IntentCollection([$fakeTriggerRestartIntent]));

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

        ScenarioDataClient::shouldReceive('addFullScenarioGraph')
            ->once()
            ->with($fakeScenario)
            ->andReturn($fakeScenarioCreated);

        ScenarioDataClient::shouldReceive('getFullScenarioGraph')
            ->once()
            ->andReturn($fakeScenarioCreated);

        ConversationDataClient::shouldReceive('updateIntent')
            ->twice()
            ->andReturn(
                $fakeTriggerWelcomeIntent,
                $fakeTriggerRestartIntent
            );

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

    public function testDuplicateScenarioFailure()
    {
        $scenario = self::getFakeScenarioForDuplication();

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

    public function testDuplicateScenarioSuccess()
    {
        $scenario = self::getFakeScenarioForDuplication();

        // Called during route binding
        ConversationDataClient::shouldReceive('getScenarioByUid')
            ->once()
            ->andReturn($scenario);

        $duplicated = null;
        ScenarioDataClient::shouldReceive('addFullScenarioGraph')
            ->once()
            ->andReturnUsing(function ($scenario) use (&$duplicated) {
                $scenario = $scenario->copy();
                $scenario->setUid('0x9999');
                $duplicated = $scenario;
                return $scenario;
            });

        // Called in controller, once before persisting, again after, and finally after patching
        ScenarioDataClient::shouldReceive('getFullScenarioGraph')
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
        $fakeScenario = self::getFakeScenario();
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
        $fakeScenario = self::getFakeScenario();

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
    public static function getFakeScenario(): Scenario
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

    public static function getFakeScenarioForDuplication(): Scenario
    {
        $scenario = self::getFakeScenario();

        $conversation = new Conversation();
        $conversation->setName("Example Conversation");
        $conversation->setUid('0x0002');
        $conversation->setOdId("example_conversation");
        $conversation->setCreatedAt(Carbon::now());
        $conversation->setUpdatedAt(Carbon::now());
        $conversation->setScenario($scenario);
        $conversations[] = $conversation;

        $scene = new Scene();
        $scene->setName("Example Scene");
        $scene->setUid('0x0003');
        $scene->setOdId("example_scene");
        $scene->setCreatedAt(Carbon::now());
        $scene->setUpdatedAt(Carbon::now());
        $scene->setConversation($conversations[0]);
        $scenes[] = $scene;

        $turn = new Turn();
        $turn->setName("Example Turn");
        $turn->setUid('0x0004');
        $turn->setOdId("example_turn");
        $turn->setCreatedAt(Carbon::now());
        $turn->setUpdatedAt(Carbon::now());
        $turn->setScene($scenes[0]);
        $turns[] = $turn;

        $requestIntent = new Intent();
        $requestIntent->setSpeaker(Intent::USER);
        $requestIntent->setIsRequestIntent(true);
        $requestIntent->setName("Example Request Intent");
        $requestIntent->setUid('0x0005');
        $requestIntent->setOdId("intent.app.exampleRequestIntent");
        $requestIntent->setCreatedAt(Carbon::now());
        $requestIntent->setUpdatedAt(Carbon::now());
        $requestIntent->setTurn($turns[0]);
        $requestIntents[] = $requestIntent;

        $responseIntent = new Intent();
        $responseIntent->setSpeaker(Intent::APP);
        $responseIntent->setIsRequestIntent(false);
        $responseIntent->setName("Example Response Intent");
        $responseIntent->setUid('0x0006');
        $responseIntent->setOdId("intent.app.exampleResponseIntent");
        $responseIntent->setCreatedAt(Carbon::now());
        $responseIntent->setUpdatedAt(Carbon::now());
        $responseIntent->setTurn($turns[0]);
        $responseIntents[] = $responseIntent;

        $message = new MessageTemplate();
        $message->setName("Example Message");
        $message->setUid('0x0007');
        $message->setOdId('example_message');
        $message->setMessageMarkup((new MessageMarkUpGenerator())->addTextMessage('Hello world')->getMarkUp());
        $message->setCreatedAt(Carbon::now());
        $message->setUpdatedAt(Carbon::now());
        $message->setIntent($responseIntent);

        $conversation = new Conversation();
        $conversation->setName("Example Conversation copy");
        $conversation->setUid('0x0002');
        $conversation->setOdId("example_conversation_copy");
        $conversation->setCreatedAt(Carbon::now());
        $conversation->setUpdatedAt(Carbon::now());
        $conversation->setScenario($scenario);
        $conversations[] = $conversation;

        $scene = new Scene();
        $scene->setName("Example Scene copy");
        $scene->setUid('0x0003');
        $scene->setOdId("example_scene_copy");
        $scene->setCreatedAt(Carbon::now());
        $scene->setUpdatedAt(Carbon::now());
        $scene->setConversation($conversations[0]);
        $scenes[] = $scene;

        $turn = new Turn();
        $turn->setName("Example Turn copy");
        $turn->setUid('0x0004');
        $turn->setOdId("example_turn_copy");
        $turn->setCreatedAt(Carbon::now());
        $turn->setUpdatedAt(Carbon::now());
        $turn->setScene($scenes[0]);
        $turns[] = $turn;

        $requestIntent = new Intent();
        $requestIntent->setIsRequestIntent(true);
        $requestIntent->setName("Example Request Intent copy");
        $requestIntent->setUid('0x0005');
        $requestIntent->setOdId("intent.app.exampleRequestIntentCopy");
        $requestIntent->setCreatedAt(Carbon::now());
        $requestIntent->setUpdatedAt(Carbon::now());
        $requestIntent->setTurn($turns[0]);
        $requestIntents[] = $requestIntent;

        $responseIntent = new Intent();
        $responseIntent->setIsRequestIntent(true);
        $responseIntent->setName("Example Response Intent copy");
        $responseIntent->setUid('0x0006');
        $responseIntent->setOdId("intent.app.exampleResponseIntentCopy");
        $responseIntent->setCreatedAt(Carbon::now());
        $responseIntent->setUpdatedAt(Carbon::now());
        $responseIntent->setTurn($turns[0]);
        $responseIntents[] = $responseIntent;

        $turns[0]->setRequestIntents(new IntentCollection($requestIntents));
        $turns[0]->setResponseIntents(new IntentCollection($responseIntents));
        $scenes[0]->setTurns(new TurnCollection($turns));
        $conversations[0]->setScenes(new SceneCollection($scenes));
        $scenario->setConversations(new ConversationCollection($conversations));

        return $scenario;
    }
}
