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
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\IntentCollection;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\SceneCollection;
use OpenDialogAi\Core\Conversation\Turn;
use OpenDialogAi\Core\Conversation\TurnCollection;
use Tests\TestCase;

class UIStateControllerTest extends TestCase
{
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
    }


    public function testFocusedConversationNotFound()
    {
        $this->markTestSkipped(
            'Currently the exception thrown in the ConversationDataClient isnt the correct one'
        );
        ConversationDataClient::shouldReceive('getScenarioWithFocusedConversation')
            ->once()
            ->with('test', false)
            ->andThrow(new ConversationObjectNotFoundException());

        $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/conversation-builder/ui-state/focused/conversation/test')
            ->assertStatus(404);
    }

    public function testGetFocusedConversation()
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

        $fakeConversation->setScenario($fakeScenario);

        ConversationDataClient::shouldReceive('getConversationByUid')
            ->once()
            ->with($fakeConversation->getUid(), false)
            ->andReturn($fakeConversation);

        ConversationDataClient::shouldReceive('getScenarioWithFocusedConversation')
            ->once()
            ->with($fakeConversation->getUid())
            ->andReturn($fakeConversation);

        $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/conversation-builder/ui-state/focused/conversation/' . $fakeConversation->getUid())
            ->assertJson([
                'scenario' => [
                    'id'=> '0x0001',
                    'od_id'=> 'example_scenario',
                    'name'=> 'Example scenario',
                    'description'=> 'An example scenario',
                   'focusedConversation' => [
                       "id" => "0x0002",
                       "name" => "New Example conversation",
                       "od_id" => "new_example_conversation",
                       "description" => "An new example conversation",
                       "interpreter" => "interpreter.core.nlp",
                       "behaviors" => [],
                       "conditions" => [],
                       "created_at" => "2021-03-12T11:57:23+0000",
                       "updated_at" => "2021-03-12T11:57:23+0000"
                   ]
                ]
            ]);
    }

    public function testGetFocusedScene()
    {
        $fakeScenario = new Scenario();
        $fakeScenario->setUid('0x0001');
        $fakeScenario->setName("Example scenario");
        $fakeScenario->setOdId('example_scenario');
        $fakeScenario->setDescription('An example scenario');

        $fakeScene = new Scene();
        $fakeScene->setUid('0x0003');
        $fakeScene->setOdId('welcome_scene');
        $fakeScene->setName('Welcome scene');
        $fakeScene->setDescription('A welcome scene');
        $fakeScene->setInterpreter('interpreter.core.nlp');
        $fakeScene->setBehaviors(new BehaviorsCollection());
        $fakeScene->setConditions(new ConditionCollection());
        $fakeScene->setCreatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $fakeScene->setUpdatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $fakeScene->setTurns(new TurnCollection());



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

        $fakeConversation->setScenario($fakeScenario);
        $fakeScene->setConversation($fakeConversation);

        ConversationDataClient::shouldReceive('getSceneByUid')
            ->once()
            ->with($fakeScene->getUid(), false)
            ->andReturn($fakeScene);


        ConversationDataClient::shouldReceive('getScenarioWithFocusedScene')
            ->once()
            ->with($fakeScene->getUid())
            ->andReturn($fakeScene);

        $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/conversation-builder/ui-state/focused/scene/' . $fakeScene->getUid())
            ->assertJson([
                'scenario' => [
                    'id'=> '0x0001',
                    'od_id'=> 'example_scenario',
                    'name'=> 'Example scenario',
                    'description'=> 'An example scenario',
                    'conversation' => [
                        "id" => "0x0002",
                        "od_id" => "new_example_conversation",
                        "name" => "New Example conversation",
                        "description" => "An new example conversation",
                        "focusedScene" => [
                             "id" => "0x0003",
                             "od_id"=> "welcome_scene",
                             "name"=> "Welcome scene",
                             "description"=> "A welcome scene",
                             "updated_at"=> "2021-02-24T09:30:00+0000",
                             "created_at"=> "2021-02-24T09:30:00+0000",
                             "interpreter" => 'interpreter.core.nlp',
                             "behaviors" => [],
                             "conditions" => [],
                             "turns" => []
                        ]
                    ]
                ]
            ]);
    }

    public function testGetFocusedTurn()
    {
        $fakeTurn = $this->createFakeConversation('0x0001', '0x0002', '0x0003', '0x0004');

        ConversationDataClient::shouldReceive('getTurnByUid')
            ->once()
            ->with($fakeTurn->getUid(), false)
            ->andReturn($fakeTurn);


        ConversationDataClient::shouldReceive('getScenarioWithFocusedTurn')
            ->once()
            ->with($fakeTurn->getUid())
            ->andReturn($fakeTurn);

        $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/conversation-builder/ui-state/focused/turn/' . $fakeTurn->getUid())
            ->assertJson([
                'scenario' => [
                    'id'=> '0x0001',
                    'od_id'=> 'example_scenario',
                    'name'=> 'Example scenario',
                    'description'=> 'An example scenario',
                    'conversation' => [
                        "id" => "0x0002",
                        "od_id" => "new_example_conversation",
                        "name" => "New Example conversation",
                        "description" => "An new example conversation",
                        "scene" => [
                            "id" => "0x0003",
                            "od_id"=> "welcome_scene",
                            "name"=> "Welcome scene",
                            "description"=> "A welcome scene",
                            "interpreter" => 'interpreter.core.nlp',
                            "focusedTurn" => [
                                "id" => "0x0004",
                                "od_id" => "first_turn",
                                "name" => "First turn",
                                "description" => "The first turn",
                                "updated_at"=> "2021-02-24T09:30:00+0000",
                                "created_at"=> "2021-02-24T09:30:00+0000",
                                "behaviors" => [],
                                "conditions" => [],
                                "intents" => []
                            ]

                        ]
                    ]
                ]
            ]);
    }

    public function testGetConversationTreeByScenario()
    {
        $fakeTurn = new Turn();
        $fakeTurn->setUid('0x0004');
        $fakeTurn->setOdId('first_turn');
        $fakeTurn->setName('First turn');

        $fakeScene = new Scene();
        $fakeScene->setUid('0x0003');
        $fakeScene->setOdId('welcome_scene');
        $fakeScene->setName('Welcome scene');
        $fakeScene->addTurn($fakeTurn);

        $fakeConversation = new Conversation();
        $fakeConversation->setUid('0x0002');
        $fakeConversation->setName('New Example conversation');
        $fakeConversation->setOdId('new_example_conversation');
        $fakeConversation->setScenes(new SceneCollection([$fakeScene]));

        $fakeScenario = new Scenario();
        $fakeScenario->setUid('0x0001');
        $fakeScenario->setConversations(new ConversationCollection([$fakeConversation]));

        ConversationDataClient::shouldReceive('getScenarioByUid')
            ->once()
            ->with($fakeScenario->getUid(), false)
            ->andReturn($fakeScenario);

        ConversationDataClient::shouldReceive('getConversationTreeByScenarioUid')
            ->once()
            ->with($fakeScenario->getUid())
            ->andReturn($fakeScenario);


        $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/conversation-builder/ui-state/scenarios/' . $fakeScenario->getUid() . '/tree')
            ->assertExactJson([
                "id" => "0x0001",
                "conversations" => [
                    [
                        "id" => "0x0002",
                        "od_id" => "new_example_conversation",
                        "name" => "New Example conversation",
                        "scenes" => [
                            [
                                "id" => "0x0003",
                                "od_id" => "welcome_scene",
                                "name" => "Welcome scene",
                                "turns" => [
                                    [
                                        "id" => "0x0004",
                                        "od_id" => "first_turn",
                                        "name" => "First turn"
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]);
    }

    public function testMassUpdateNoRequestResponse()
    {
        ConversationDataClient::shouldReceive('getTurnByUid')->never();

        $this->actingAs($this->user, 'api')
            ->patch('/admin/api/conversation-builder/ui-state/turns/0x0001/intents/neither', [])
            ->assertStatus(404);
    }

    public function testMassUpdateNonValidParticipant()
    {
        $fakeTurn = new Turn();
        $fakeTurn->setUid('0x0001');
        $fakeTurn->setOdId('welcome_turn');
        $fakeTurn->setName('Welcome Turn');
        $fakeTurn->setDescription('A welcome turn');

        ConversationDataClient::shouldReceive('getTurnByUid')
            ->once()
            ->with($fakeTurn->getUid(), false)
            ->andReturn($fakeTurn);

        $body = ['participant' => 'INVALID'];

        $this->actingAs($this->user, 'api')
            ->json('PATCH', '/admin/api/conversation-builder/ui-state/turns/0x0001/intents/request', $body)
            ->assertStatus(422);
    }

    public function testMassIntentUpdate()
    {
        $turnUid = '0x0004';

        $turn = $this->createFakeConversation('0x0001', '0x0002', '0x0003', $turnUid);

        $requestIntent = new Intent($turn);
        $requestIntent->setUid('0x0005');
        $requestIntent->setOdId('welcome_intent_1');
        $requestIntent->setName('Welcome intent 1');
        $requestIntent->setDescription('A welcome intent 1');
        $requestIntent->setCreatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $requestIntent->setUpdatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $requestIntent->setSpeaker(Intent::USER);
        $requestIntent->setSampleUtterance('Hello!');
        $turn->addRequestIntent($requestIntent);

        $responseIntent = new Intent($turn);
        $responseIntent->setUid('0x0006');
        $responseIntent->setOdId('goodbye_intent_1');
        $responseIntent->setName('Goodbye intent 1');
        $responseIntent->setDescription('A goodbye intent 1');
        $responseIntent->setCreatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $responseIntent->setUpdatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $responseIntent->setSpeaker(Intent::APP);
        $responseIntent->setSampleUtterance('Welcome user!');
        $turn->addResponseIntent($responseIntent);

        ConversationDataClient::shouldReceive('getTurnByUid')
            ->once()
            ->with($turn->getUid(), false)
            ->andReturn($turn);

        $responseIntent->setSpeaker(Intent::USER);
        ConversationDataClient::shouldReceive('updateIntent')
            ->once()
            ->with($responseIntent);

        $requestIntent->setSpeaker(Intent::APP);
        ConversationDataClient::shouldReceive('updateIntent')
            ->once()
            ->with($requestIntent);


        $turn->setRequestIntents(new IntentCollection());
        $turn->addRequestIntent($requestIntent);

        $turn->setResponseIntents(new IntentCollection());
        $turn->addResponseIntent($responseIntent);
        ConversationDataClient::shouldReceive('getScenarioWithFocusedTurn')
            ->once()
            ->andReturn($turn);

        $body = ['participant' => 'APP'];

        $this->actingAs($this->user, 'api')
            ->json('PATCH', "/admin/api/conversation-builder/ui-state/turns/$turnUid/intents/request", $body)
            ->assertStatus(200)
            ->assertJsonFragment([
                "intents" => [
                    [
                        "intent" => [
                            "description" => "A goodbye intent 1",
                            "id" => "0x0006",
                            "name" => "Goodbye intent 1",
                            "od_id" => "goodbye_intent_1",
                            "sample_utterance" => "Welcome user!",
                            "speaker" => "USER"
                        ],
                        "order" => "RESPONSE"
                    ],
                    [
                        "intent" => [
                            "description" => "A welcome intent 1",
                            "id" => "0x0005",
                            "name" => "Welcome intent 1",
                            "od_id" => "welcome_intent_1",
                            "sample_utterance" => "Hello!",
                            "speaker" => "APP"
                        ],
                        "order" => "REQUEST"
                    ]
                ]
            ]);
    }

    /**
     * @param $scenarioUid
     * @param $conversationUid
     * @param $sceneUid
     * @param $turnUid
     * @return Turn
     */
    public function createFakeConversation($scenarioUid, $conversationUid, $sceneUid, $turnUid): Turn
    {
        $fakeScenario = new Scenario();
        $fakeScenario->setUid($scenarioUid);
        $fakeScenario->setName("Example scenario");
        $fakeScenario->setOdId('example_scenario');
        $fakeScenario->setDescription('An example scenario');

        $fakeConversation = new Conversation();
        $fakeConversation->setUid($conversationUid);
        $fakeConversation->setName('New Example conversation');
        $fakeConversation->setOdId('new_example_conversation');
        $fakeConversation->setDescription("An new example conversation");

        $fakeConversation->setScenario($fakeScenario);

        $fakeScene = new Scene();
        $fakeScene->setUid($sceneUid);
        $fakeScene->setOdId('welcome_scene');
        $fakeScene->setName('Welcome scene');
        $fakeScene->setDescription('A welcome scene');
        $fakeScene->setInterpreter('interpreter.core.nlp');

        $fakeScene->setConversation($fakeConversation);

        $fakeTurn = new Turn();
        $fakeTurn->setUid($turnUid);
        $fakeTurn->setOdId('first_turn');
        $fakeTurn->setName('First turn');
        $fakeTurn->setDescription('The first turn');
        $fakeTurn->setInterpreter('interpreter.core.nlp');
        $fakeTurn->setBehaviors(new BehaviorsCollection());
        $fakeTurn->setConditions(new ConditionCollection());
        $fakeTurn->setCreatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $fakeTurn->setUpdatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));

        $fakeTurn->setRequestIntents(new IntentCollection());
        $fakeTurn->setResponseIntents(new IntentCollection());

        $fakeTurn->setScene($fakeScene);
        return $fakeTurn;
    }
}
