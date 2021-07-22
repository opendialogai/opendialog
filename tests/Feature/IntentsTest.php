<?php


namespace Tests\Feature;

use App\User;
use Carbon\Carbon;
use OpenDialogAi\Core\Conversation\BehaviorsCollection;
use OpenDialogAi\Core\Conversation\ConditionCollection;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Exceptions\ConversationObjectNotFoundException;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\Facades\IntentDataClient;
use OpenDialogAi\Core\Conversation\Facades\MessageTemplateDataClient;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\IntentCollection;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\Transition;
use OpenDialogAi\Core\Conversation\Turn;
use OpenDialogAi\Core\Conversation\VirtualIntent;
use Tests\TestCase;

class IntentsTest extends TestCase
{
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
    }

    public function testGetIntentsRequiresAuthentication()
    {
        $this->get('/admin/api/conversation-builder/intents/trigger302')
            ->assertStatus(302);
    }

    public function testGetIntentNotFound()
    {
        ConversationDataClient::shouldReceive('getIntentByUid')
            ->once()
            ->with('test', false)
            ->andThrow(new ConversationObjectNotFoundException());

        $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/conversation-builder/intents/test')
            ->assertStatus(404);
    }


    public function testGetAllIntentsByTurn()
    {
        $fakeTurn = new Turn();
        $fakeTurn->setUid('0x0004');
        $fakeTurn->setName('New Example turn 1');
        $fakeTurn->setOdId('new_example_turn_1');
        $fakeTurn->setDescription("An new example turn 1");

        $fakeRequestIntent = $this->createIntent($fakeTurn, '0x0005', 'welcome_intent_1', Intent::USER);

        $fakeResponseIntent = new Intent($fakeTurn);
        $fakeResponseIntent->setUid('0x0006');
        $fakeResponseIntent->setOdId('goodbye_intent_1');
        $fakeResponseIntent->setName('Goodbye intent 1');
        $fakeResponseIntent->setDescription('A goodbye intent 1');
        $fakeResponseIntent->setCreatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $fakeResponseIntent->setUpdatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $fakeResponseIntent->setInterpreter('interpreter.core.nlp');
        $fakeResponseIntent->setConditions(new ConditionCollection());
        $fakeResponseIntent->setBehaviors(new BehaviorsCollection());
        $fakeResponseIntent->setSpeaker(Intent::APP);
        $fakeResponseIntent->setConfidence(1.0);
        $fakeResponseIntent->setListensFor(['intent_c']);
        $fakeResponseIntent->setTransition(new Transition(null, null, null));
        $fakeResponseIntent->setVirtualIntent(VirtualIntent::createEmpty());
        $fakeResponseIntent->setSampleUtterance('Welcome user!');

        $fakeRequestIntentCollection = new IntentCollection();
        $fakeRequestIntentCollection->addObject($fakeRequestIntent);

        $fakeResponseIntentCollection = new IntentCollection();
        $fakeResponseIntentCollection->addObject($fakeResponseIntent);

        ConversationDataClient::shouldReceive('getTurnByUid')
            ->once()
            ->with($fakeTurn->getUid(), false)
            ->andReturn($fakeTurn);

        ConversationDataClient::shouldReceive('getAllRequestIntentsByTurn')
            ->once()
            ->andReturn($fakeRequestIntentCollection);

        ConversationDataClient::shouldReceive('getAllResponseIntentsByTurn')
            ->once()
            ->andReturn($fakeResponseIntentCollection);

        $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/conversation-builder/turns/' . $fakeTurn->getUid() . '/intents')
            ->assertStatus(200)
            ->assertJson([[
                'order' => 'REQUEST',
                'intent' => [
                    "id" => "0x0005",
                    "od_id" => "welcome_intent_1",
                    "name" => "Welcome intent 1",
                    "description" => "A welcome intent 1",
                    "interpreter" => 'interpreter.core.nlp',
                    "created_at" => "2021-02-24T09:30:00+0000",
                    "updated_at" => "2021-02-24T09:30:00+0000",
                    "conditions" => [],
                    "behaviors" => [],
                    "speaker" => 'USER',
                    "confidence" => 1,
                    "listens_for" => ['intent_a', 'intent_b'],
                    "transition" => [],
                    "virtual_intent" => [],
                    "sample_utterance" => "Hello!"
                ],
            ], [
                'order' => 'RESPONSE',
                'intent' => [
                    "id" => "0x0006",
                    "od_id" => "goodbye_intent_1",
                    "name" => "Goodbye intent 1",
                    "description" => "A goodbye intent 1",
                    "interpreter" => 'interpreter.core.nlp',
                    "created_at" => "2021-02-24T09:30:00+0000",
                    "updated_at" => "2021-02-24T09:30:00+0000",
                    "conditions" => [],
                    "behaviors" => [],
                    "speaker" => 'APP',
                    "confidence" => 1,
                    "listens_for" => ['intent_c'],
                    "transition" => [],
                    "virtual_intent" => [],
                    "sample_utterance" => "Welcome user!"
    ],
]]);
    }

    public function testAddRequestIntentToTurn()
    {
        $fakeTurn = new Turn();
        $fakeTurn->setUid('0x0004');
        $fakeTurn->setName('New Example turn 1');
        $fakeTurn->setOdId('new_example_turn_1');
        $fakeTurn->setDescription("An new example turn 1");

        $fakeRequestIntent = $this->createIntent($fakeTurn, '0x0005', 'welcome_intent_1', Intent::USER);

        ConversationDataClient::shouldReceive('getTurnByUid')
            ->once()
            ->with($fakeTurn->getUid(), false)
            ->andReturn($fakeTurn);

        ConversationDataClient::shouldReceive();
        ConversationDataClient::shouldReceive('addRequestIntent')
            ->once()
            ->withAnyArgs()
            ->andReturn($fakeRequestIntent);

        $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/conversation-builder/turns/' . $fakeTurn->getUid() . '/intents', [
                "order" => "REQUEST",
                "intent" => [
                    "od_id" => "welcome_intent_1",
                    "name" => "Welcome intent 1",
                    "description" => "A welcome intent 1",
                    "default_interpreter" => "interpreter.core.nlp",
                    "conditions" => [],
                    "behaviors" => [],
                    "listens_for" => ["intent_a", "intent_b"],
                    "speaker" => "USER",
                    "confidence" => 1,
                    "sample_utterance" => "Hello!"
                ]

            ])
            //->assertStatus(200)
            ->assertJson([
                "order" => "REQUEST",
                "intent" => [
                    "id" => "0x0005",
                    "od_id" => "welcome_intent_1",
                    "name" => "Welcome intent 1",
                    "description" => "A welcome intent 1",
                    "interpreter" => "interpreter.core.nlp",
                    "created_at" => "2021-02-24T09:30:00+0000",
                    "updated_at" => "2021-02-24T09:30:00+0000",
                    "conditions" => [],
                    "behaviors" => [],
                    "listens_for" => ["intent_a", "intent_b"],
                    "speaker" => "USER",
                    "confidence" => 1,
                    "sample_utterance" => "Hello!"
                ]
            ]);
    }

    public function testAddResponseIntentToTurn()
    {
        $fakeTurn = new Turn();
        $fakeTurn->setUid('0x0004');
        $fakeTurn->setName('New Example turn 1');
        $fakeTurn->setOdId('new_example_turn_1');
        $fakeTurn->setDescription("An new example turn 1");

        $fakeResponseIntent = new Intent($fakeTurn);
        $fakeResponseIntent->setUid('0x0005');
        $fakeResponseIntent->setOdId('goodbye_intent_1');
        $fakeResponseIntent->setName('Goodbye intent 1');
        $fakeResponseIntent->setDescription('A goodbye intent 1');
        $fakeResponseIntent->setCreatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $fakeResponseIntent->setUpdatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $fakeResponseIntent->setInterpreter('interpreter.core.nlp');
        $fakeResponseIntent->setConditions(new ConditionCollection());
        $fakeResponseIntent->setBehaviors(new BehaviorsCollection());
        $fakeResponseIntent->setSpeaker(Intent::APP);
        $fakeResponseIntent->setConfidence(1.0);
        $fakeResponseIntent->setListensFor(['intent_a', 'intent_b']);
        $fakeResponseIntent->setTransition(new Transition(null, null, null));
        $fakeResponseIntent->setVirtualIntent(VirtualIntent::createEmpty());
        $fakeResponseIntent->setSampleUtterance('Bye!');

        ConversationDataClient::shouldReceive('getTurnByUid')
            ->once()
            ->with($fakeTurn->getUid(), false)
            ->andReturn($fakeTurn);

        ConversationDataClient::shouldReceive();
        ConversationDataClient::shouldReceive('addResponseIntent')
            ->once()
            ->withAnyArgs()
            ->andReturn($fakeResponseIntent);

        MessageTemplateDataClient::shouldReceive('addMessageTemplateToIntent')
            ->once()
            ->withAnyArgs();

        $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/conversation-builder/turns/' . $fakeTurn->getUid() . '/intents', [
                "order" => "RESPONSE",
                "intent" => [
                    "od_id" => "goodbye_intent_1",
                    "name" => "Goodbye intent 1",
                    "description" => "A goodbye intent 1",
                    "default_interpreter" => "interpreter.core.nlp",
                    "conditions" => [],
                    "behaviors" => [],
                    "listens_for" => ["intent_a", "intent_b"],
                    "speaker" => "APP",
                    "confidence" => 1,
                    "sample_utterance" => "Bye!"
                ]

            ])
            //->assertStatus(200)
            ->assertJson([
                "order" => "RESPONSE",
                "intent" => [
                    "id" => "0x0005",
                    "od_id" => "goodbye_intent_1",
                    "name" => "Goodbye intent 1",
                    "description" => "A goodbye intent 1",
                    "interpreter" => "interpreter.core.nlp",
                    "created_at" => "2021-02-24T09:30:00+0000",
                    "updated_at" => "2021-02-24T09:30:00+0000",
                    "conditions" => [],
                    "behaviors" => [],
                    "listens_for" => ["intent_a", "intent_b"],
                    "speaker" => "APP",
                    "confidence" => 1,
                    "sample_utterance" => "Bye!"
                ]
            ]);
    }

    public function testAddResponseIntentToTurnVirtualIntentValidation()
    {
        $fakeTurn = new Turn();
        $fakeTurn->setUid('0x0004');
        $fakeTurn->setName('New Example turn 1');
        $fakeTurn->setOdId('new_example_turn_1');
        $fakeTurn->setDescription("An new example turn 1");

        ConversationDataClient::shouldReceive('getTurnByUid')
            ->once()
            ->with($fakeTurn->getUid(), false)
            ->andReturn($fakeTurn);

        $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/conversation-builder/turns/' . $fakeTurn->getUid() . '/intents', [
                "order" => "RESPONSE",
                "intent" => [
                    "od_id" => "goodbye_intent_1",
                    "name" => "Goodbye intent 1",
                    "description" => "A goodbye intent 1",
                    "default_interpreter" => "interpreter.core.nlp",
                    "conditions" => [],
                    "behaviors" => [],
                    "listens_for" => ["intent_a", "intent_b"],
                    "speaker" => "APP",
                    "confidence" => 1,
                    "sample_utterance" => "Bye!",
                    "virtual_intent" => [
                        "speaker" => "APP",
                        "intent_id" => "test"
                    ]
                ]

            ])
            ->assertStatus(422)
            ->assertJson([
                "message" => "The given data was invalid.",
                "errors" => [
                    "intent.virtual_intent.speaker" => [
                        "The virtual intent speaker can only be USER."
                    ]
                ]
            ]);
    }

    public function testGetTurnIntentByTurnAndIntentUid()
    {
        $fakeTurn = new Turn();
        $fakeTurn->setUid('0x0004');
        $fakeTurn->setName('New Example turn 1');
        $fakeTurn->setOdId('new_example_turn_1');
        $fakeTurn->setDescription("An new example turn 1");

        $fakeRequestIntent = $this->createIntent($fakeTurn, '0x0005', 'welcome_intent_1', Intent::USER);

        $fakeTurnWithFakeRequestIntent = $fakeTurn;
        $fakeTurnWithFakeRequestIntent->addRequestIntent($fakeRequestIntent);

        ConversationDataClient::shouldReceive('getTurnByUid')
            ->once()
            ->with($fakeTurn->getUid(), false)
            ->andReturn($fakeTurn);

        ConversationDataClient::shouldReceive('getIntentByUid')
            ->once()
            ->with($fakeRequestIntent->getUid(), false)
            ->andReturn($fakeRequestIntent);

        ConversationDataClient::shouldReceive('getTurnWithIntent')
            ->once()
            ->withAnyArgs()
            ->andReturn($fakeTurnWithFakeRequestIntent);

        $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/conversation-builder/turns/' . $fakeTurn->getUid() . '/turn-intents/' .
$fakeRequestIntent->getUid())
            //            ->assertStatus(200)
            ->assertJson([
                'order' => 'REQUEST',
                'intent' => [
                    "id" => "0x0005",
                    "od_id" => "welcome_intent_1",
                    "name" => "Welcome intent 1",
                    "description" => "A welcome intent 1",
                    "interpreter" => 'interpreter.core.nlp',
                    "created_at" => "2021-02-24T09:30:00+0000",
                    "updated_at" => "2021-02-24T09:30:00+0000",
                    "conditions" => [],
                    "behaviors" => [],
                    "speaker" => 'USER',
                    "confidence" => 1,
                    "listens_for" => ['intent_a', 'intent_b'],
                    "transition" => [],
                    "virtual_intent" => [],
                    "sample_utterance" => "Hello!"
                ],
            ]);
    }

    public function testUpdateTurnIntentByTurnAndIntentUid()
    {
        $fakeTurn = new Turn();
        $fakeTurn->setUid('0x0004');
        $fakeTurn->setName('New Example turn 1');
        $fakeTurn->setOdId('new_example_turn_1');
        $fakeTurn->setDescription("An new example turn 1");

        $fakeRequestIntent = $this->createIntent($fakeTurn, '0x0005', 'welcome_intent_1', Intent::USER);

        $fakeUpdatedResponseIntent = clone($fakeRequestIntent);
        $fakeUpdatedResponseIntent->setOdId('welcome_intent_updated');
        $fakeUpdatedResponseIntent->setName('Welcome intent 1 Updated');
        $fakeUpdatedResponseIntent->setDescription('A welcome intent updated');
        $fakeUpdatedResponseIntent->setListensFor(['intent_a_updated', 'intent_b']);
        $fakeUpdatedResponseIntent->setSampleUtterance('Hello Updated!');

        $fakeUpdatedTurn = $fakeTurn;
        $fakeUpdatedTurn->setRequestIntents(new IntentCollection());
        $fakeUpdatedTurn->addResponseIntent($fakeUpdatedResponseIntent);

        ConversationDataClient::shouldReceive('getTurnByUid')
            ->once()
            ->with($fakeTurn->getUid(), false)
            ->andReturn($fakeTurn);

        ConversationDataClient::shouldReceive('getIntentByUid')
            ->once()
            ->with($fakeRequestIntent->getUid(), false)
            ->andReturn($fakeRequestIntent);

        ConversationDataClient::shouldReceive('updateIntent')
            ->once()
            ->withAnyArgs()
            ->andReturn($fakeUpdatedResponseIntent);

        ConversationDataClient::shouldReceive('updateTurnIntentRelation')
            ->once()
            ->withAnyArgs()
            ->andReturn($fakeUpdatedTurn);

        $this->actingAs($this->user, 'api')
            ->json('PATCH', '/admin/api/conversation-builder/turns/' . $fakeTurn->getUid() . '/turn-intents/' .
                $fakeRequestIntent->getUid(), [
                    'order' => 'RESPONSE',
                    'intent' => [
                        'name' => $fakeUpdatedResponseIntent->getName(),
                        'id' => $fakeUpdatedResponseIntent->getUid(),
                        'od_id' => $fakeUpdatedResponseIntent->getOdId(),
                        'description' =>  $fakeUpdatedResponseIntent->getDescription(),
                        'confidence' =>  $fakeUpdatedResponseIntent->getConfidence(),
                        'speaker' =>  $fakeUpdatedResponseIntent->getSpeaker(),
                        'listens_for' => $fakeUpdatedResponseIntent->getListensFor(),
                        'sample_utterance' => $fakeUpdatedResponseIntent->getSampleUtterance()
                    ]
            ])
            ->assertJson([
                'order' => 'RESPONSE',
                'intent' => [
                    "id" => "0x0005",
                    "od_id" => "welcome_intent_updated",
                    "name" => "Welcome intent 1 Updated",
                    "description" => "A welcome intent updated",
                    "interpreter" => "interpreter.core.nlp",
                    "created_at" => "2021-02-24T09:30:00+0000",
                    "updated_at" => "2021-02-24T09:30:00+0000",
                    "speaker" => "USER",
                    "confidence" => 1,
                    "conditions" => [],
                    "behaviors" => [],
                    "sample_utterance" => "Hello Updated!",
                    "listens_for" => ["intent_a_updated", "intent_b"],
                    "transition" => [],
                    "virtual_intent" => [],
                ]
            ]);
    }

    public function testGetIntentByUid()
    {
        $fakeIntent = new Intent();
        $fakeIntent->setUid('0x0005');
        $fakeIntent->setOdId('welcome_intent');
        $fakeIntent->setName('Welcome Intent');
        $fakeIntent->setDescription('A welcome intent');
        $fakeIntent->setCreatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $fakeIntent->setUpdatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $fakeIntent->setInterpreter('interpreter.core.nlp');
        $fakeIntent->setConditions(new ConditionCollection());
        $fakeIntent->setBehaviors(new BehaviorsCollection());
        $fakeIntent->setSpeaker(Intent::USER);
        $fakeIntent->setConfidence(1.0);
        $fakeIntent->setListensFor(['intent_a', 'intent_b']);
        $fakeIntent->setTransition(new Transition(null, null, null));
        $fakeIntent->setVirtualIntent(VirtualIntent::createEmpty());
        $fakeIntent->setSampleUtterance('Hello!');

        ConversationDataClient::shouldReceive('getIntentByUid')
            ->once()
            ->with($fakeIntent->getUid(), false)
            ->andReturn($fakeIntent);

        $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/conversation-builder/intents/' . $fakeIntent->getUid())
            ->assertJson([
                "id" => "0x0005",
                "od_id" => "welcome_intent",
                "name" => "Welcome Intent",
                "description" => "A welcome intent",
                "interpreter" => "interpreter.core.nlp",
                "created_at" => "2021-02-24T09:30:00+0000",
                "updated_at" => "2021-02-24T09:30:00+0000",
                "conditions" => [],
                "behaviors" => [],
                "listens_for" => ['intent_a', 'intent_b'],
                "speaker" => "USER",
                "confidence" => 1,
                "sample_utterance" => "Hello!",
                "transition" => [],
                "virtual_intent" => [],
            ]);
    }

    public function testUpdateIntentByUid()
    {
        $fakeIntent = new Intent();
        $fakeIntent->setUid('0x0005');
        $fakeIntent->setOdId('welcome_intent');
        $fakeIntent->setName('Welcome Intent');
        $fakeIntent->setDescription('A welcome intent');
        $fakeIntent->setCreatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $fakeIntent->setUpdatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $fakeIntent->setInterpreter('interpreter.core.nlp');
        $fakeIntent->setConditions(new ConditionCollection());
        $fakeIntent->setBehaviors(new BehaviorsCollection());
        $fakeIntent->setSpeaker(Intent::USER);
        $fakeIntent->setConfidence(1.0);
        $fakeIntent->setListensFor(['intent_a', 'intent_b']);
        $fakeIntent->setTransition(new Transition(null, null, null));
        $fakeIntent->setVirtualIntent(VirtualIntent::createEmpty());
        $fakeIntent->setSampleUtterance('Hello!');

        $fakeIntentUpdated = new Intent();
        $fakeIntentUpdated->setUid('0x0005');
        $fakeIntentUpdated->setOdId('welcome_intent_updated');
        $fakeIntentUpdated->setName('Welcome Intent Updated');
        $fakeIntentUpdated->setDescription('An updated welcome intent');
        $fakeIntentUpdated->setCreatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $fakeIntentUpdated->setUpdatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $fakeIntentUpdated->setInterpreter('interpreter.core.nlp');
        $fakeIntentUpdated->setConditions(new ConditionCollection());
        $fakeIntentUpdated->setBehaviors(new BehaviorsCollection());
        $fakeIntentUpdated->setSpeaker(Intent::USER);
        $fakeIntentUpdated->setConfidence(1.0);
        $fakeIntentUpdated->setListensFor(['intent_a_updated', 'intent_b']);
        $fakeIntentUpdated->setTransition(new Transition(null, null, null));
        $fakeIntentUpdated->setVirtualIntent(VirtualIntent::createEmpty());
        $fakeIntentUpdated->setSampleUtterance('Hello updated!');

        ConversationDataClient::shouldReceive('getIntentByUid')
            ->once()
            ->with($fakeIntent->getUid(), false)
            ->andReturn($fakeIntent);

        ConversationDataClient::shouldReceive('updateIntent')
            ->once()
            ->withAnyArgs()
            ->andReturn($fakeIntentUpdated);

        $this->actingAs($this->user, 'api')
            ->json('PATCH', '/admin/api/conversation-builder/intents/' . $fakeIntent->getUid(), [
                'name' => $fakeIntentUpdated->getName(),
                'id' => $fakeIntentUpdated->getUid(),
                'od_id' => $fakeIntentUpdated->getOdId(),
                'description' =>  $fakeIntentUpdated->getDescription(),
                'confidence' =>  $fakeIntentUpdated->getConfidence(),
                'speaker' =>  $fakeIntentUpdated->getSpeaker(),
                'listens_for' => $fakeIntentUpdated->getListensFor(),
                'sample_utterance' => $fakeIntentUpdated->getSampleUtterance()
            ])
            //->assertStatus(200)
            ->assertJson([
                "id" => "0x0005",
                "od_id" => "welcome_intent_updated",
                "name" => "Welcome Intent Updated",
                "description" => "An updated welcome intent",
                "interpreter" => "interpreter.core.nlp",
                "created_at" => "2021-02-24T09:30:00+0000",
                "updated_at" => "2021-02-24T09:30:00+0000",
                "speaker" => "USER",
                "confidence" => 1,
                "conditions" => [],
                "behaviors" => [],
                "sample_utterance" => "Hello updated!",
                "listens_for" => ["intent_a_updated", "intent_b"],
                "transition" => [],
                "virtual_intent" => [],
            ]);
    }

    public function testDeleteIntentByUid()
    {
        $fakeIntent = new Intent();
        $fakeIntent->setUid('0x0005');
        $fakeIntent->setOdId('welcome_intent');
        $fakeIntent->setName('Welcome Intent');
        $fakeIntent->setDescription('A welcome intent');
        $fakeIntent->setCreatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $fakeIntent->setUpdatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $fakeIntent->setInterpreter('interpreter.core.nlp');
        $fakeIntent->setConditions(new ConditionCollection());
        $fakeIntent->setBehaviors(new BehaviorsCollection());
        $fakeIntent->setSpeaker(Intent::USER);
        $fakeIntent->setConfidence(1.0);
        $fakeIntent->setListensFor(['intent_a', 'intent_b']);
        $fakeIntent->setTransition(new Transition(null, null, null));
        $fakeIntent->setVirtualIntent(VirtualIntent::createEmpty());
        $fakeIntent->setSampleUtterance('Hello!');

        ConversationDataClient::shouldReceive('getIntentByUid')
            ->once()
            ->with($fakeIntent->getUid(), false)
            ->andReturn($fakeIntent);

        ConversationDataClient::shouldReceive('deleteIntentByUid')
            ->once()
            ->with($fakeIntent->getUid())
            ->andReturn(true);

        $this->actingAs($this->user, 'api')
            ->json('DELETE', '/admin/api/conversation-builder/intents/' . $fakeIntent->getUid())
            ->assertStatus(200);
    }

    public function testDuplicationIncomingIntent()
    {
        $scenario = ScenariosTest::getFakeScenarioForDuplication();

        /** @var Conversation $conversation */
        $conversation = $scenario->getConversations()->getObjectsWithId('example_conversation')->first();

        /** @var Scene $scene */
        $scene = $conversation->getScenes()->getObjectsWithId('example_scene')->first();

        /** @var Turn $turn */
        $turn = $scene->getTurns()->getObjectsWithId('example_turn')->first();

        /** @var Intent $intent */
        $intent = $turn->getRequestIntents()->getObjectsWithId('intent.app.exampleRequestIntent')->first();

        // Called during route binding
        ConversationDataClient::shouldReceive('getIntentByUid')
            ->once()
            ->andReturn($intent);

        // Called in controller, once before persisting and again after
        IntentDataClient::shouldReceive('getFullIntentGraph')
            ->twice()
            ->andReturnUsing(function ($uid) use ($intent) {
                $intent->setUid($uid);
                return $intent;
            });

        IntentDataClient::shouldReceive('addFullIntentGraph')
            ->once()
            ->andReturnUsing(function ($intent) {
                $intent->setUid('0x9999');
                return $intent;
            });

        // The OD ID should be the same because it is used for interpretation purposes and is therefore not expected to be unique
        $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/conversation-builder/intents/' . $intent->getUid() . '/duplicate')
            ->assertStatus(200)
            ->assertJson([
                'name' => 'Example Request Intent',
                'od_id' => 'intent.app.exampleRequestIntent',
                'id'=> '0x9999',
            ]);
    }

    public function testDuplicationOutgoingIntent()
    {
        $scenario = ScenariosTest::getFakeScenarioForDuplication();

        /** @var Conversation $conversation */
        $conversation = $scenario->getConversations()->getObjectsWithId('example_conversation')->first();

        /** @var Scene $scene */
        $scene = $conversation->getScenes()->getObjectsWithId('example_scene')->first();

        /** @var Turn $turn */
        $turn = $scene->getTurns()->getObjectsWithId('example_turn')->first();

        /** @var Intent $intent */
        $intent = $turn->getResponseIntents()->getObjectsWithId('intent.app.exampleResponseIntent')->first();

        // Called during route binding
        ConversationDataClient::shouldReceive('getIntentByUid')
            ->once()
            ->andReturn($intent);

        // Called in the controller, getting parent & sibling data
        ConversationDataClient::shouldReceive('getTurnByUid')
            ->once()
            ->andReturnUsing(function ($uid) use ($turn) { return $turn; });

        // Called in controller, once before persisting and again after
        IntentDataClient::shouldReceive('getFullIntentGraph')
            ->twice()
            ->andReturnUsing(function ($uid) use ($intent) {
                $intent->setUid($uid);
                return $intent;
            });

        IntentDataClient::shouldReceive('addFullIntentGraph')
            ->once()
            ->andReturnUsing(function ($intent) {
                $intent->setUid('0x9999');
                return $intent;
            });

        // The OD ID should be the same because it is used for interpretation purposes and is therefore not expected to be unique
        $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/conversation-builder/intents/' . $intent->getUid() . '/duplicate')
            ->assertStatus(200)
            ->assertJson([
                'name' => 'Example Response IntentCopy',
                'od_id' => 'intent.app.exampleResponseIntentCopy',
                'id'=> '0x9999',
            ]);
    }
}
