<?php


namespace Tests\Feature;

use App\Console\Facades\ImportExportSerializer;
use Illuminate\Support\Carbon;
use OpenDialogAi\Core\Conversation\Behavior;
use OpenDialogAi\Core\Conversation\BehaviorsCollection;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\ConversationCollection;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\IntentCollection;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\SceneCollection;
use OpenDialogAi\Core\Conversation\Transition;
use OpenDialogAi\Core\Conversation\Turn;
use OpenDialogAi\Core\Conversation\TurnCollection;
use OpenDialogAi\Core\Conversation\VirtualIntent;
use OpenDialogAi\Core\Conversation\VirtualIntentCollection;
use Tests\TestCase;

class ImportExportSerializerTest extends TestCase
{
    public function getFullTestScenario() {
        /**
         * Scenario
         *  - Conversation A
         *   - Scene A
         *     - Turn A
         *      - Intent A (Request)
         *      - Intent B (Response)
         *     - Turn B
         *      - Intent C (Request)
         *   - Scene B
         *    - Turn C
         *     - Intent D (Response)
         *  - Conversation B
         *   - Scene C
         *    - Turn D
         */
        $scenario = new Scenario();
        $scenario->setOdId("test_scenario_full");
        $scenario->setName("Test scenario (Full)");
        $scenario->setDescription("Test scenario description.");
        $scenario->setInterpreter("interpreter.core.nlp");
        $scenario->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::STARTING_BEHAVIOR), new Behavior(
            Behavior::OPEN_BEHAVIOR)]));
        $scenario->setActive(true);
        $scenario->setStatus(Scenario::LIVE_STATUS);

        // Conversations
        $conversationA = new \OpenDialogAi\Core\Conversation\Conversation($scenario);
        $conversationA->setOdId("test_conversation_a");
        $conversationA->setName("Test conversation (A)");
        $conversationA->setDescription("(A) Test conversation description.");
        $conversationA->setInterpreter("interpreter.core.nlp");
        $conversationA->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::STARTING_BEHAVIOR)]));

        $conversationB = new Conversation($scenario);
        $conversationB->setOdId("test_conversation_b");
        $conversationB->setName("Test conversation (B)");
        $conversationB->setDescription("(B) Test conversation description.");
        $conversationB->setInterpreter("interpreter.core.nlp");
        $conversationB->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::STARTING_BEHAVIOR)]));

        $scenario->setConversations(new ConversationCollection([$conversationA, $conversationB]));

        // Scenes
        $sceneA = new Scene($conversationA);
        $sceneA->setOdId("test_scene_a");
        $sceneA->setName("Test scene (A)");
        $sceneA->setDescription("(A) Test scene description.");
        $sceneA->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::STARTING_BEHAVIOR)]));

        $sceneB = new Scene($conversationA);
        $sceneB->setOdId("test_scene_b");
        $sceneB->setName("Test scene (B)");
        $sceneB->setDescription("(B) Test scene description.");
        $sceneB->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::STARTING_BEHAVIOR)]));

        $conversationA->setScenes(new SceneCollection([$sceneA, $sceneB]));

        $sceneC = new Scene($conversationB);
        $sceneC->setOdId("test_scene_c");
        $sceneC->setName("Test scene (C)");
        $sceneC->setDescription("(C) Test scene description.");
        $sceneC->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::STARTING_BEHAVIOR)]));

        $conversationB->setScenes(new SceneCollection([$sceneC]));

        // Turns
        $turnA = new Turn($sceneA);
        $turnA->setOdId("test_turn_a");
        $turnA->setName("Test turn (A)");
        $turnA->setDescription("(A) Test turn description.");
        $turnA->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::STARTING_BEHAVIOR)]));

        $turnB = new Turn($sceneA);
        $turnB->setOdId("test_turn_b");
        $turnB->setName("Test turn (B)");
        $turnB->setDescription("(B) Test turn description.");
        $turnB->setValidOrigins(["test_turn_a"]);

        $sceneA->setTurns(new TurnCollection([$turnA, $turnB]));

        $turnC = new Turn($sceneB);
        $turnC->setOdId("test_turn_c");
        $turnC->setName("Test turn (C)");
        $turnC->setDescription("(C) Test turn description.");
        $turnC->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::STARTING_BEHAVIOR)]));

        $sceneB->setTurns(new TurnCollection([$turnC]));

        $turnD = new Turn($sceneC);
        $turnD->setOdId("test_turn_d");
        $turnD->setName("Test turn (D)");
        $turnD->setDescription("(D) Test turn description.");
        $turnD->setValidOrigins(["test_turn_a", "test_turn_c"]);

        $sceneC->setTurns(new TurnCollection([$turnD]));


        //Intents
        $intentA = new Intent($turnA);
        $intentA->setOdId("test_intent_a");
        $intentA->setName("Test intent (A)");
        $intentA->setDescription("(A) Test intent description.");
        $intentA->setSpeaker(Intent::USER);
        $intentA->setConfidence(1.0);
        $intentA->setSampleUtterance("(A) Test intent sample utterance");
        $intentA->setTransition(new Transition(null, null, "test_turn_d"));
        $intentA->setVirtualIntents(new VirtualIntentCollection([new VirtualIntent(Intent::USER, "test_intent_b")]));

        $intentB = new Intent($turnA);
        $intentB->setOdId("test_intent_b");
        $intentB->setName("Test intent (B)");
        $intentB->setDescription("(B) Test intent description.");
        $intentB->setSpeaker(Intent::APP);
        $intentB->setConfidence(1.0);
        $intentB->setSampleUtterance("(B) Test intent sample utterance");

        $turnA->setRequestIntents(new IntentCollection([$intentA]));
        $turnA->setResponseIntents(new IntentCollection([$intentB]));

        $intentC = new Intent($turnB);
        $intentC->setOdId("test_intent_c");
        $intentC->setName("Test intent (C)");
        $intentC->setDescription("(C) Test intent description.");
        $intentC->setSpeaker(Intent::USER);
        $intentC->setConfidence(1.0);
        $intentC->setSampleUtterance("(C) Test intent sample utterance");

        $turnB->setRequestIntents(new IntentCollection([$intentC]));

        $intentD = new Intent($turnC);
        $intentD->setOdId("test_intent_D");
        $intentD->setName("Test intent (D)");
        $intentD->setDescription("(D) Test intent description.");
        $intentD->setSpeaker(Intent::APP);
        $intentD->setConfidence(1.0);
        $intentD->setSampleUtterance("(D) Test intent sample utterance");

        $turnC->setResponseIntents(new IntentCollection([$intentD]));

        return $scenario;
    }

    public function getMinimalTestScenario() {
        $scenario = new Scenario();
        $scenario->setOdId("test_scenario_minimal");
        $scenario->setName("Test Scenario (Minimal)");
        $scenario->setActive(true);
        $scenario->setStatus(Scenario::LIVE_STATUS);
        return $scenario;
    }

    public function getTestScenarioToIntentBranch() {
        $scenario = new Scenario();
        $scenario->setOdId("test_scenario_to_intent");
        $scenario->setName("Test Scenario (Down to intent)");
        $scenario->setActive(true);
        $scenario->setStatus(Scenario::LIVE_STATUS);

        $conversation = new Conversation($scenario);
        $conversation->setOdId("test_conversation");
        $conversation->setName("Test conversation");
        $scenario->setConversations(new ConversationCollection([$conversation]));

        $scene = new Scene($conversation);
        $scene->setOdId("test_scene");
        $scene->setName("Test scene");
        $conversation->setScenes(new SceneCollection([$scene]));

        $turn = new Turn($scene);
        $turn->setOdId("test_turn");
        $turn->setName("Test turn");
        $turn->setValidOrigins(['origin_a', 'origin_b']);
        $scene->setTurns(new TurnCollection([$turn]));

        $intent = new Intent($turn);
        $intent->setOdId("test_request_intent");
        $intent->setName("Test request intent");
        $intent->setSampleUtterance("Sample utterance (Test)");
        $intent->setConfidence(1.0);
        $intent->setSpeaker(Intent::USER);

        $turn->addRequestIntent($intent);

        return $scenario;
    }

    public function testSerializeIgnoresTimestamps() {
        $scenario = $this->getTestScenarioToIntentBranch();
        $scenario->setUpdatedAt(Carbon::now());
        $scenario->setCreatedAt(Carbon::now());
        $conversation = $scenario->getConversations()[0];
        $conversation->setUpdatedAt(Carbon::now());
        $conversation->setCreatedAt(Carbon::now());
        $scene = $conversation->getScenes()[0];
        $scene->setUpdatedAt(Carbon::now());
        $scene->setCreatedAt(Carbon::now());
        $turn = $scene->getTurns()[0];
        $turn->setUpdatedAt(Carbon::now());
        $turn->setCreatedAt(Carbon::now());
        $intent = $turn->getRequestIntents()[0];
        $intent->setUpdatedAt(Carbon::now());
        $intent->setCreatedAt(Carbon::now());

        $serializedScenario = ImportExportSerializer::serialize($scenario, 'json');
        $normalizedScenario = json_decode($serializedScenario, JSON_THROW_ON_ERROR);        $this->assertIsArray($normalizedScenario);
        $this->assertArrayNotHasKey('updated_at', $normalizedScenario);
        $this->assertArrayNotHasKey('created_at', $normalizedScenario);
        $normalizedConversation = $normalizedScenario['conversations'][0];
        $this->assertArrayNotHasKey('updated_at', $normalizedConversation);
        $this->assertArrayNotHasKey('created_at', $normalizedConversation);
        $normalizedScene = $normalizedConversation['scenes'][0];
        $this->assertArrayNotHasKey('updated_at', $normalizedScene);
        $this->assertArrayNotHasKey('created_at', $normalizedScene);
        $normalizedTurn = $normalizedScene['turns'][0];
        $this->assertArrayNotHasKey('updated_at', $normalizedTurn);
        $this->assertArrayNotHasKey('created_at', $normalizedTurn);
        $normalizedIntent = $normalizedTurn['request_intents'][0];
        $this->assertArrayNotHasKey('updated_at', $normalizedIntent);
        $this->assertArrayNotHasKey('created_at', $normalizedIntent);
    }

    public function testSerializeIgnoresScenarioStatusActive() {
        $scenario = new Scenario();
        $scenario->setOdId("test_scenario");
        $scenario->setName("Test Scenario");
        $scenario->setStatus(Scenario::LIVE_STATUS);
        $scenario->setActive(true);

        $serializedScenario = ImportExportSerializer::serialize($scenario, 'json');
        $normalizedScenario = json_decode($serializedScenario, JSON_THROW_ON_ERROR);        $this->assertIsArray($normalizedScenario);
        $this->assertArrayNotHasKey('status', $normalizedScenario);
        $this->assertArrayNotHasKey('active', $normalizedScenario);
    }

    public function testSerializeMinimalScenario() {
        $scenario = $this->getMinimalTestScenario();

        $serializedScenario = ImportExportSerializer::serialize($scenario, 'json');

        $normalizedScenario = json_decode($serializedScenario, JSON_THROW_ON_ERROR);
        $this->assertIsArray($normalizedScenario);
        $this->assertEquals([
            'od_id' => 'test_scenario_minimal',
            'name' => 'Test Scenario (Minimal)',
            'description' => '',
            'interpreter' => '',
            'conditions' => [],
            'behaviors' => [],
            'conversations' => []
        ], $normalizedScenario);
    }

    public function testSerializeScenarioToIntent() {
        $scenario = $this->getTestScenarioToIntentBranch();
        $serializedScenario = ImportExportSerializer::serialize($scenario, 'json');

        $normalizedScenario = json_decode($serializedScenario, JSON_THROW_ON_ERROR);
        $this->assertIsArray($normalizedScenario);
        $this->assertEquals([
            'od_id' => 'test_scenario_to_intent',
            'name' => 'Test Scenario (Down to intent)',
            'description' => '',
            'interpreter' => '',
            'conditions' => [],
            'behaviors' => [],
            'conversations' => [
                [
                    'od_id' => 'test_conversation',
                    'name' => 'Test conversation',
                    'description' => '',
                    'interpreter' => '',
                    'conditions' => [],
                    'behaviors' => [],
                    'scenes' => [
                        [
                            'od_id' => 'test_scene',
                            'name' => 'Test scene',
                            'description' => '',
                            'interpreter' => '',
                            'conditions' => [],
                            'behaviors' => [],
                            'turns' => [
                                [
                                    'od_id' => 'test_turn',
                                    'name' => 'Test turn',
                                    'description' => '',
                                    'interpreter' => '',
                                    'conditions' => [],
                                    'behaviors' => [],
                                    'valid_origins' => ['origin_a', 'origin_b'],
                                    'request_intents' => [
                                        [
                                            'od_id' => 'test_request_intent',
                                            'name' => 'Test request intent',
                                            'description' => '',
                                            'interpreter' => '',
                                            'conditions' => [],
                                            'behaviors' => [],
                                            'sample_utterance' => 'Sample utterance (Test)',
                                            'confidence' => 1,
                                            'speaker' => 'USER',
                                            'listens_for' => [],
                                            'expected_attributes' => [],
                                            'transition' => null,
                                            'virtual_intents' => [],
                                            'actions' => []
                                        ]
                                    ],
                                    'response_intents' => []
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ], $normalizedScenario);
    }

    public function testSerializeFullScenario() {
        $scenario = $this->getFullTestScenario();
        $serializedScenario = ImportExportSerializer::serialize($scenario, 'json');

        $normalizedScenario = json_decode($serializedScenario, JSON_THROW_ON_ERROR);
        $this->assertIsArray($normalizedScenario);

        $this->assertEquals([
                "od_id" => "test_scenario_full",
                "name" => "Test scenario (Full)",
                "description" => "Test scenario description.",
                "interpreter" => "interpreter.core.nlp",
                "conditions" => [],
                "behaviors" => [
                    "STARTING",
                    "OPEN"
                ],
                "conversations" => [
                    [
                        "od_id" => "test_conversation_a",
                        "name" => "Test conversation (A)",
                        "description" => "(A) Test conversation description.",
                        "interpreter" => "interpreter.core.nlp",
                        "conditions" => [],
                        "behaviors" => ["STARTING"],
                        "scenes" => [
                            [
                                "od_id" => "test_scene_a",
                                "name" => "Test scene (A)",
                                "description" => "(A) Test scene description.",
                                "interpreter" => "interpreter.core.nlp",
                                "conditions" => [],
                                "behaviors" => ["STARTING"],
                                "turns" => [
                                    [
                                        "od_id" => "test_turn_a",
                                        "name" => "Test turn (A)",
                                        "description" => "(A) Test turn description.",
                                        "interpreter" => "interpreter.core.nlp",
                                        "conditions" => [],
                                        "behaviors" => ["STARTING"],
                                        "valid_origins" => [],
                                        "request_intents" => [
                                            [
                                                "od_id" => "test_intent_a",
                                                "name" => "Test intent (A)",
                                                "description" => "(A) Test intent description.",
                                                "interpreter" => "interpreter.core.nlp",
                                                "conditions" => [],
                                                "behaviors" => [],
                                                "speaker" => "USER",
                                                "confidence" => 1,
                                                "sample_utterance" => "(A) Test intent sample utterance",
                                                "listens_for" => [],
                                                "expected_attributes" => [],
                                                "transition" => [
                                                    "conversation" => null,
                                                    "scene" => null,
                                                    "turn" => "test_turn_d"
                                                ],
                                                "virtual_intents" => [
                                                    [
                                                        "speaker" => "USER",
                                                        "intentId" => "test_intent_b"
                                                    ]
                                                ],
                                                "actions" => []
                                            ]
                                        ],
                                        "response_intents" => [
                                            [
                                                "od_id" => "test_intent_b",
                                                "name" => "Test intent (B)",
                                                "description" => "(B) Test intent description.",
                                                "interpreter" => "interpreter.core.nlp",
                                                "conditions" => [],
                                                "behaviors" => [],
                                                "speaker" => "APP",
                                                "confidence" => 1,
                                                "sample_utterance" => "(B) Test intent sample utterance",
                                                "listens_for" => [],
                                                "expected_attributes" => [],
                                                "transition" => null,
                                                "virtual_intents" => [],
                                                "actions" => []
                                            ]
                                        ]
                                    ],
                                    [
                                        "od_id" => "test_turn_b",
                                        "name" => "Test turn (B)",
                                        "description" => "(B) Test turn description.",
                                        "interpreter" => "interpreter.core.nlp",
                                        "conditions" => [],
                                        "behaviors" => [],
                                        "valid_origins" => ["test_turn_a"],
                                        "request_intents" => [
                                            [
                                                "od_id" => "test_intent_c",
                                                "name" => "Test intent (C)",
                                                "description" => "(C) Test intent description.",
                                                "interpreter" => "interpreter.core.nlp",
                                                "conditions" => [],
                                                "behaviors" => [],
                                                "speaker" => "USER",
                                                "confidence" => 1,
                                                "sample_utterance" => "(C) Test intent sample utterance",
                                                "listens_for" => [],
                                                "expected_attributes" => [],
                                                "transition" => null,
                                                "virtual_intents" => [],
                                                "actions" => []
                                            ]
                                        ],
                                        "response_intents" => []
                                    ]
                                ]
                            ],
                            [
                                "od_id" => "test_scene_b",
                                "name" => "Test scene (B)",
                                "description" => "(B) Test scene description.",
                                "interpreter" => "interpreter.core.nlp",
                                "conditions" => [],
                                "behaviors" => ["STARTING"],
                                "turns" => [
                                    [
                                        "od_id" => "test_turn_c",
                                        "name" => "Test turn (C)",
                                        "description" => "(C) Test turn description.",
                                        "interpreter" => "interpreter.core.nlp",
                                        "conditions" => [],
                                        "behaviors" => ["STARTING"],
                                        "valid_origins" => [],
                                        "request_intents" => [],
                                        "response_intents" => [
                                            [
                                                "od_id" => "test_intent_D",
                                                "name" => "Test intent (D)",
                                                "description" => "(D) Test intent description.",
                                                "interpreter" => "interpreter.core.nlp",
                                                "conditions" => [],
                                                "behaviors" => [],
                                                "speaker" => "APP",
                                                "confidence" => 1,
                                                "sample_utterance" => "(D) Test intent sample utterance",
                                                "listens_for" => [],
                                                "expected_attributes" => [],
                                                "transition" => null,
                                                "virtual_intents" => [],
                                                "actions" => []
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        "od_id" => "test_conversation_b",
                        "name" => "Test conversation (B)",
                        "description" => "(B) Test conversation description.",
                        "interpreter" => "interpreter.core.nlp",
                        "conditions" => [],
                        "behaviors" => ["STARTING"],
                        "scenes" => [
                            [
                                "od_id" => "test_scene_c",
                                "name" => "Test scene (C)",
                                "description" => "(C) Test scene description.",
                                "interpreter" => "interpreter.core.nlp",
                                "conditions" => [],
                                "behaviors" => ["STARTING"],
                                "turns" => [
                                    [
                                        "od_id" => "test_turn_d",
                                        "name" => "Test turn (D)",
                                        "description" => "(D) Test turn description.",
                                        "interpreter" => "interpreter.core.nlp",
                                        "conditions" => [],
                                        "behaviors" => [],
                                        "valid_origins" => [
                                            "test_turn_a",
                                            "test_turn_c"
                                        ],
                                        "request_intents" => [],
                                        "response_intents" => []
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
        ], $normalizedScenario);

    }

}
