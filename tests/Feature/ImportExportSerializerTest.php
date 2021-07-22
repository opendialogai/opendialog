<?php


namespace Tests\Feature;

use App\Console\Facades\ImportExportSerializer;
use App\ImportExportHelpers\PathSubstitutionHelper;
use Illuminate\Support\Carbon;
use OpenDialogAi\Core\Conversation\Behavior;
use OpenDialogAi\Core\Conversation\BehaviorsCollection;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\ConversationCollection;
use OpenDialogAi\Core\Conversation\ConversationObject;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\IntentCollection;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\SceneCollection;
use OpenDialogAi\Core\Conversation\Transition;
use OpenDialogAi\Core\Conversation\Turn;
use OpenDialogAi\Core\Conversation\TurnCollection;
use OpenDialogAi\Core\Conversation\VirtualIntent;
use RuntimeException;
use Tests\TestCase;

class ImportExportSerializerTest extends TestCase
{
    public static function getConversationObjectPropertyByName(string $name, ConversationObject $object)
    {
        if (!in_array($name, $object::allFields())) {
            throw new RuntimeException("Can't access property %s of %s", $name, get_class($object));
        }
        $nameToPropertyValue = [
            ConversationObject::UID => fn (ConversationObject $object) => $object->getUid(),
            ConversationObject::OD_ID => fn (ConversationObject $object) => $object->getOdId(),
            ConversationObject::NAME => fn (ConversationObject $object) => $object->getName(),
            ConversationObject::DESCRIPTION => fn (ConversationObject $object) => $object->getDescription(),
            ConversationObject::INTERPRETER => fn (ConversationObject $object) => $object->getInterpreter(),
            ConversationObject::BEHAVIORS => fn (ConversationObject $object) => $object->getBehaviors(),
            ConversationObject::CONDITIONS => fn (ConversationObject $object) => $object->getConditions(),
        ];

        return $nameToPropertyValue[$name]($object);
    }

    public static function getScenarioPropertyByName(string $name, Scenario $object)
    {
        if (!in_array($name, $object::allFields())) {
            throw new RuntimeException("Can't access property %s of %s", $name, get_class($object));
        }
        if (in_array($name, ConversationObject::allFields())) {
            return self::getConversationObjectPropertyByName($name, $object);
        } else {
            $nameToPropertyValue = [
                Scenario::ACTIVE => fn (Scenario $scenario) => $scenario->isActive(),
                Scenario::STATUS => fn (Scenario $scenario) => $scenario->getStatus(),
                Scenario::CONVERSATIONS => fn (Scenario $scenario) => $scenario->getConversations()
            ];
            return $nameToPropertyValue[$name]($object);
        }
    }

    public static function getConversationPropertyByName(string $name, Conversation $object)
    {
        if (!in_array($name, $object::allFields())) {
            throw new RuntimeException("Can't access property %s of %s", $name, get_class($object));
        }
        if (in_array($name, ConversationObject::allFields())) {
            return self::getConversationObjectPropertyByName($name, $object);
        } else {
            $nameToPropertyValue = [
                Conversation::SCENARIO => fn (Conversation $conversation) => $conversation->getScenario(),
                Conversation::SCENES => fn (Conversation $conversation) => $conversation->getScenes(),
            ];
            return $nameToPropertyValue[$name]($object);
        }
    }

    public static function getScenePropertyByName(string $name, Scene $object)
    {
        if (!in_array($name, $object::allFields())) {
            throw new RuntimeException("Can't access property %s of %s", $name, get_class($object));
        }
        if (in_array($name, ConversationObject::allFields())) {
            return self::getConversationObjectPropertyByName($name, $object);
        } else {
            $nameToPropertyValue = [
                Scene::CONVERSATION => fn (Scene $scene) => $scene->getConversation(),
                Scene::TURNS => fn (Scene $scene) => $scene->getTurns(),
            ];
            return $nameToPropertyValue[$name]($object);
        }
    }

    public static function getTurnPropertyByName(string $name, Turn $object)
    {
        if (!in_array($name, $object::allFields())) {
            throw new RuntimeException("Can't access property %s of %s", $name, get_class($object));
        }
        if (in_array($name, ConversationObject::allFields())) {
            return self::getConversationObjectPropertyByName($name, $object);
        } else {
            $nameToPropertyValue = [
                Turn::SCENE => fn (Turn $turn) => $turn->getScene(),
                Turn::REQUEST_INTENTS => fn (Turn $turn) => $turn->getRequestIntents(),
                Turn::RESPONSE_INTENTS => fn (Turn $turn) => $turn->getResponseIntents(),
                Turn::VALID_ORIGINS => fn (Turn $turn) => $turn->getValidOrigins()
            ];
            return $nameToPropertyValue[$name]($object);
        }
    }

    public static function getIntentPropertyByName(string $name, Intent $object)
    {
        if (!in_array($name, $object::allFields())) {
            throw new RuntimeException("Can't access property %s of %s", $name, get_class($object));
        }
        if (in_array($name, ConversationObject::allFields())) {
            return self::getConversationObjectPropertyByName($name, $object);
        } else {
            $nameToPropertyValue = [
                Intent::SPEAKER => fn (Intent $intent) => $intent->getSpeaker(),
                Intent::CONFIDENCE => fn (Intent $intent) => $intent->getConfidence(),
                Intent::SAMPLE_UTTERANCE => fn (Intent $intent) => $intent->getSampleUtterance(),
                Intent::LISTENS_FOR => fn (Intent $intent) => $intent->getListensFor(),
                Intent::EXPECTED_ATTRIBUTES => fn (Intent $intent) => $intent->getExpectedAttributes(),
                Intent::TRANSITION => fn (Intent $intent) => $intent->getTransition(),
                Intent::VIRTUAL_INTENT => fn (Intent $intent) => $intent->getVirtualIntent(),
                Intent::ACTIONS => fn (Intent $intent) => $intent->getActions(),
                Intent::TURN => fn (Intent $intent) => $intent->getTurn()
            ];
            return $nameToPropertyValue[$name]($object);
        }
    }

    public function assertNullTimestamps(ConversationObject $object)
    {
        $this->assertNull($object->getUpdatedAt());
        $this->assertNull($object->getCreatedAt());
    }

    public function assertEqualFields(ConversationObject $expected, ConversationObject $actual, array $fields)
    {
        foreach ($fields as $field) {
            if ($expected instanceof Scenario && $actual instanceof Scenario) {
                $this->assertEquals(
                    self::getScenarioPropertyByName($field, $expected),
                    self::getScenarioPropertyByName($field, $actual)
                );
            }

            if ($expected instanceof Conversation && $actual instanceof Conversation) {
                $this->assertEquals(
                    self::getConversationPropertyByName($field, $expected),
                    self::getConversationPropertyByName($field, $actual)
                );
            }

            if ($expected instanceof Scene && $actual instanceof Scene) {
                $this->assertEquals(
                    self::getScenePropertyByName($field, $expected),
                    self::getScenePropertyByName($field, $actual)
                );
            }

            if ($expected instanceof Turn && $actual instanceof Turn) {
                $this->assertEquals(self::getTurnPropertyByName($field, $expected), self::getTurnPropertyByName($field, $actual));
            }

            if ($expected instanceof Intent && $actual instanceof Intent) {
                $this->assertEquals(
                    self::getIntentPropertyByName($field, $expected),
                    self::getIntentPropertyByName($field, $actual)
                );
            }
        }
    }

    public function getMinimalTestScenario()
    {
        $scenario = new Scenario();
        $scenario->setOdId("test_scenario_minimal");
        $scenario->setName("Test Scenario (Minimal)");
        $scenario->setActive(true);
        $scenario->setStatus(Scenario::LIVE_STATUS);
        return $scenario;
    }

    public function getTestScenarioToIntentBranch()
    {
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
        $turn->setValidOrigins([
            'origin_a',
            'origin_b'
        ]);
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

    public function getFullTestScenario()
    {
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
        $scenario->setBehaviors(new BehaviorsCollection([
            new Behavior(Behavior::STARTING_BEHAVIOR),
            new Behavior(Behavior::OPEN_BEHAVIOR)
        ]));
        $scenario->setActive(true);
        $scenario->setStatus(Scenario::LIVE_STATUS);

        // Conversations
        $conversationA = new Conversation($scenario);
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

        $scenario->setConversations(new ConversationCollection([
            $conversationA,
            $conversationB
        ]));

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

        $conversationA->setScenes(new SceneCollection([
            $sceneA,
            $sceneB
        ]));

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

        $sceneA->setTurns(new TurnCollection([
            $turnA,
            $turnB
        ]));

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
        $turnD->setValidOrigins([
            "test_turn_a",
            "test_turn_c"
        ]);

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
        $intentA->setVirtualIntent(new VirtualIntent(Intent::USER, "test_intent_b"));

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

    public function getSerializedMinimalTestScenario()
    {
        return ImportExportSerializer::serialize($this->getMinimalTestScenario(), 'json');
    }

    public function getSerializedTestScenarioToIntentBranch()
    {
        return ImportExportSerializer::serialize($this->getTestScenarioToIntentBranch(), 'json');
    }

    public function getSerializedFullTestScenario()
    {
        return ImportExportSerializer::serialize($this->getFullTestScenario(), 'json');
    }

    public function testSerializeIgnoresTimestamps()
    {
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
        $normalizedScenario = json_decode($serializedScenario, JSON_THROW_ON_ERROR);
        $this->assertIsArray($normalizedScenario);
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

    public function testSerializeIgnoresScenarioStatusActive()
    {
        $scenario = new Scenario();
        $scenario->setOdId("test_scenario");
        $scenario->setName("Test Scenario");
        $scenario->setStatus(Scenario::LIVE_STATUS);
        $scenario->setActive(true);

        $serializedScenario = ImportExportSerializer::serialize($scenario, 'json');
        $normalizedScenario = json_decode($serializedScenario, JSON_THROW_ON_ERROR);
        $this->assertIsArray($normalizedScenario);
        $this->assertArrayNotHasKey('status', $normalizedScenario);
        $this->assertArrayNotHasKey('active', $normalizedScenario);
    }

    public function testSerializeMinimalScenario()
    {
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

    public function testSerializeScenarioToIntent()
    {
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
                                    'valid_origins' => [
                                        'origin_a',
                                        'origin_b'
                                    ],
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
                                            'actions' => [],
                                            'message_templates' => []
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

    public function testSerializeFullScenario()
    {
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
                            "interpreter" => "",
                            "conditions" => [],
                            "behaviors" => ["STARTING"],
                            "turns" => [
                                [
                                    "od_id" => "test_turn_a",
                                    "name" => "Test turn (A)",
                                    "description" => "(A) Test turn description.",
                                    "interpreter" => "",
                                    "conditions" => [],
                                    "behaviors" => ["STARTING"],
                                    "valid_origins" => [],
                                    "request_intents" => [
                                        [
                                            "od_id" => "test_intent_a",
                                            "name" => "Test intent (A)",
                                            "description" => "(A) Test intent description.",
                                            "interpreter" => "",
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
                                            "virtual_intent" => [
                                                "speaker" => "USER",
                                                "intent_id" => "test_intent_b"
                                            ],
                                            "actions" => [],
                                            "message_templates" => []
                                        ]
                                    ],
                                    "response_intents" => [
                                        [
                                            "od_id" => "test_intent_b",
                                            "name" => "Test intent (B)",
                                            "description" => "(B) Test intent description.",
                                            "interpreter" => "",
                                            "conditions" => [],
                                            "behaviors" => [],
                                            "speaker" => "APP",
                                            "confidence" => 1,
                                            "sample_utterance" => "(B) Test intent sample utterance",
                                            "listens_for" => [],
                                            "expected_attributes" => [],
                                            "actions" => [],
                                            "message_templates" => []
                                        ]
                                    ]
                                ],
                                [
                                    "od_id" => "test_turn_b",
                                    "name" => "Test turn (B)",
                                    "description" => "(B) Test turn description.",
                                    "interpreter" => "",
                                    "conditions" => [],
                                    "behaviors" => [],
                                    "valid_origins" => ["test_turn_a"],
                                    "request_intents" => [
                                        [
                                            "od_id" => "test_intent_c",
                                            "name" => "Test intent (C)",
                                            "description" => "(C) Test intent description.",
                                            "interpreter" => "",
                                            "conditions" => [],
                                            "behaviors" => [],
                                            "speaker" => "USER",
                                            "confidence" => 1,
                                            "sample_utterance" => "(C) Test intent sample utterance",
                                            "listens_for" => [],
                                            "expected_attributes" => [],
                                            "actions" => [],
                                            'message_templates' => []
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
                            "interpreter" => "",
                            "conditions" => [],
                            "behaviors" => ["STARTING"],
                            "turns" => [
                                [
                                    "od_id" => "test_turn_c",
                                    "name" => "Test turn (C)",
                                    "description" => "(C) Test turn description.",
                                    "interpreter" => "",
                                    "conditions" => [],
                                    "behaviors" => ["STARTING"],
                                    "valid_origins" => [],
                                    "request_intents" => [],
                                    "response_intents" => [
                                        [
                                            "od_id" => "test_intent_D",
                                            "name" => "Test intent (D)",
                                            "description" => "(D) Test intent description.",
                                            "interpreter" => "",
                                            "conditions" => [],
                                            "behaviors" => [],
                                            "speaker" => "APP",
                                            "confidence" => 1,
                                            "sample_utterance" => "(D) Test intent sample utterance",
                                            "listens_for" => [],
                                            "expected_attributes" => [],
                                            "actions" => [],
                                            'message_templates' => []
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
                            "interpreter" => "",
                            "conditions" => [],
                            "behaviors" => ["STARTING"],
                            "turns" => [
                                [
                                    "od_id" => "test_turn_d",
                                    "name" => "Test turn (D)",
                                    "description" => "(D) Test turn description.",
                                    "interpreter" => "",
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

    public function testDeserializeMinimalScenario()
    {
        $serializedScenario = $this->getSerializedMinimalTestScenario();

        /* @var $deserializedScenario Scenario */
        $deserializedScenario = ImportExportSerializer::deserialize($serializedScenario, Scenario::class, 'json');

        $minimalScenario = $this->getMinimalTestScenario();

        // There should be no Uid
        $this->assertNull($deserializedScenario->getUid());
        // Status/Active should be reset.
        $this->assertEquals(Scenario::DRAFT_STATUS, $deserializedScenario->getStatus());
        $this->assertEquals(false, $deserializedScenario->isActive());

        // CreatedAt/UpdatedAt should be null
        $this->assertNullTimestamps($deserializedScenario);

        // Other fields should match minimalScenario
        $this->assertEqualFields($minimalScenario, $deserializedScenario, [
            Scenario::OD_ID,
            Scenario::NAME,
            Scenario::DESCRIPTION,
            Scenario::INTERPRETER,
            Scenario::BEHAVIORS,
            Scenario::CONDITIONS,
            Scenario::CONVERSATIONS
        ]);
    }

    public function testDeserializeScenarioToIntentBranch()
    {
        $serializedScenario = $this->getSerializedTestScenarioToIntentBranch();

        /* @var $deserializedScenario Scenario */
        $deserializedScenario = ImportExportSerializer::deserialize($serializedScenario, Scenario::class, 'json');

        $scenarioToIntentBranch = $this->getTestScenarioToIntentBranch();

        $this->assertEqualFields($scenarioToIntentBranch, $deserializedScenario, [
            Scenario::OD_ID,
            Scenario::NAME,
            Scenario::DESCRIPTION,
            Scenario::INTERPRETER,
            Scenario::BEHAVIORS,
            Scenario::CONDITIONS
        ]);
        $this->assertNull($deserializedScenario->getUid());
        $this->assertNullTimestamps($deserializedScenario);
    }

    public function testDeserializeFullScenarioGraph()
    {
        $serializedScenario = $this->getSerializedFullTestScenario();

        /* @var $deserializedScenario Scenario */
        $deserializedScenario = ImportExportSerializer::deserialize($serializedScenario, Scenario::class, 'json');
        $fullScenario = $this->getFullTestScenario();

        // Scenario Assertions
        $this->assertEqualFields($fullScenario, $deserializedScenario, [
            Scenario::OD_ID,
            Scenario::NAME,
            Scenario::DESCRIPTION,
            Scenario::INTERPRETER,
            Scenario::BEHAVIORS,
            Scenario::CONDITIONS
        ]);
        $this->assertNull($deserializedScenario->getUid());
        $this->assertNullTimestamps($deserializedScenario);

        $this->assertEquals($fullScenario->getConversations()->count(), $deserializedScenario->getConversations()->count());

        // Conversation Assertions

        /* @var $deserializedConversation Conversation */
        foreach ($deserializedScenario->getConversations() as $deserializedConversation) {
            $this->assertEquals($deserializedScenario, $deserializedConversation->getScenario());
            $this->assertNullTimestamps($deserializedConversation);
            $this->assertNull($deserializedConversation->getUid());
            $matchingConversation = $fullScenario->getConversations()->filter(fn ($conversation) => $conversation->getOdId() ===
                $deserializedConversation->getOdId())->first();
            $this->assertNotNull($matchingConversation);
            $this->assertEqualFields($matchingConversation, $deserializedConversation, [
                Conversation::OD_ID,
                Conversation::NAME,
                Conversation::DESCRIPTION,
                Conversation::INTERPRETER,
                Conversation::BEHAVIORS,
                Conversation::CONDITIONS
            ]);

            // Scene Assertions

            /* @var $deserializedScene Scene */
            foreach ($deserializedConversation->getScenes() as $deserializedScene) {
                $this->assertEquals($deserializedConversation, $deserializedScene->getConversation());
                $this->assertNullTimestamps($deserializedScene);
                $this->assertNull($deserializedScene->getUid());
                /* @var $matchingScene Scene */
                $matchingScene =
                    $matchingConversation->getScenes()->filter(fn ($scene) => $scene->getOdId() === $deserializedScene->getOdId())
                        ->first();
                $this->assertNotNull($matchingScene);
                $this->assertEqualFields($matchingScene, $deserializedScene, [
                    Scene::OD_ID,
                    Scene::NAME,
                    Scene::DESCRIPTION,
                    Scene::INTERPRETER,
                    Scene::BEHAVIORS,
                    Scene::CONDITIONS
                ]);

                // Turn Assertions

                /* @var $deserializedTurn Turn */
                foreach ($deserializedScene->getTurns() as $deserializedTurn) {
                    $this->assertEquals($deserializedScene, $deserializedTurn->getScene());
                    $this->assertNullTimestamps($deserializedTurn);
                    $this->assertNull($deserializedTurn->getUid());
                    /* @var $matchingTurn Turn */
                    $matchingTurn =
                        $matchingScene->getTurns()->filter(fn ($turn) => $turn->getOdId() === $deserializedTurn->getOdId())
                            ->first();
                    $this->assertNotNull($matchingTurn);
                    $this->assertEqualFields($matchingTurn, $deserializedTurn, [
                        Turn::OD_ID,
                        Turn::NAME,
                        Turn::DESCRIPTION,
                        Turn::INTERPRETER,
                        Turn::BEHAVIORS,
                        Turn::CONDITIONS,
                        Turn::VALID_ORIGINS
                    ]);

                    // Intent Assertions

                    /* @var $deserializedRequestIntent Intent */
                    foreach ($deserializedTurn->getRequestIntents() as $deserializedRequestIntent) {
                        $this->assertEquals($deserializedTurn, $deserializedRequestIntent->getTurn());
                        $this->assertNullTimestamps($deserializedRequestIntent);
                        $this->assertNull($deserializedRequestIntent->getUid());
                        /* @var $matchingRequestIntent Intent */
                        $matchingRequestIntent = $matchingTurn->getRequestIntents()->filter(fn ($turn) => $turn->getOdId() ===
                            $deserializedRequestIntent->getOdId())->first();
                        $this->assertNotNull($matchingRequestIntent);
                        $this->assertEqualFields($matchingRequestIntent, $deserializedRequestIntent, [
                            Intent::OD_ID,
                            Intent::NAME,
                            Intent::DESCRIPTION,
                            Intent::INTERPRETER,
                            Intent::BEHAVIORS,
                            Intent::CONDITIONS,
                            Intent::SPEAKER,
                            Intent::CONFIDENCE,
                            Intent::SAMPLE_UTTERANCE,
                            Intent::LISTENS_FOR,
                            Intent::EXPECTED_ATTRIBUTES,
                            Intent::VIRTUAL_INTENT,
                            Intent::TRANSITION
                        ]);
                    }
                    /* @var $deserializedResponseIntent Intent */
                    foreach ($deserializedTurn->getResponseIntents() as $deserializedResponseIntent) {
                        $this->assertEquals($deserializedTurn, $deserializedResponseIntent->getTurn());
                        $this->assertNullTimestamps($deserializedResponseIntent);
                        $this->assertNull($deserializedResponseIntent->getUid());
                        /* @var $matchingResponseIntent Intent */
                        $matchingResponseIntent = $matchingTurn->getResponseIntents()->filter(fn ($turn) => $turn->getOdId() ===
                            $deserializedResponseIntent->getOdId())->first();
                        $this->assertNotNull($matchingResponseIntent);
                        $this->assertEqualFields($matchingResponseIntent, $deserializedResponseIntent, [
                            Intent::OD_ID,
                            Intent::NAME,
                            Intent::DESCRIPTION,
                            Intent::INTERPRETER,
                            Intent::BEHAVIORS,
                            Intent::CONDITIONS,
                            Intent::SPEAKER,
                            Intent::CONFIDENCE,
                            Intent::SAMPLE_UTTERANCE,
                            Intent::LISTENS_FOR,
                            Intent::EXPECTED_ATTRIBUTES,
                            Intent::VIRTUAL_INTENT,
                            Intent::TRANSITION
                        ]);
                    }
                }
            }
        }
    }

    public function testMap()
    {
        $scenarioUid = "0x123";
        $scenario = new Scenario();
        $scenario->setOdId("example_scenario");
        $scenario->setUid($scenarioUid);
        $expectedScenarioPath = '$path:example_scenario';

        $conversationUid = "0x124";
        $conversation = new Conversation($scenario);
        $conversation->setOdId("example_conversation");
        $conversation->setUid($conversationUid);
        $expectedConversationPath = '$path:example_scenario/example_conversation';

        $scene1Uid = "0x125";
        $scene1 = new Scene($conversation);
        $scene1->setOdId("example_scene1");
        $scene1->setUid($scene1Uid);
        $expectedScene1Path = '$path:example_scenario/example_conversation/example_scene1';

        $turn1Uid = "0x126";
        $turn1 = new Turn($scene1);
        $turn1->setOdId("example_turn");
        $turn1->setUid($turn1Uid);
        $expectedTurn1Path = '$path:example_scenario/example_conversation/example_scene1/example_turn';

        $scene2Uid = "0x127";
        $scene2 = new Scene($conversation);
        $scene2->setOdId("example_scene2");
        $scene2->setUid($scene2Uid);
        $expectedScene2Path = '$path:example_scenario/example_conversation/example_scene2';

        $turn2Uid = "0x128";
        $turn2 = new Turn($scene2);
        $turn2->setOdId("example_turn");
        $turn2->setUid($turn2Uid);
        $expectedTurn2Path = '$path:example_scenario/example_conversation/example_scene2/example_turn';

        $scenario->setConversations(new ConversationCollection([$conversation]));
        $conversation->setScenes(new SceneCollection([$scene1, $scene2]));
        $scene1->setTurns(new TurnCollection([$turn1]));
        $scene2->setTurns(new TurnCollection([$turn2]));

        $map = PathSubstitutionHelper::createScenarioMap($scenario);

        $this->assertArrayHasKey($scenarioUid, $map);
        $this->assertEquals($expectedScenarioPath, $map->get($scenarioUid));
        $this->assertArrayHasKey($expectedScenarioPath, $map);
        $this->assertEquals($scenarioUid, $map->get($expectedScenarioPath));

        $this->assertArrayHasKey($conversationUid, $map);
        $this->assertEquals($expectedConversationPath, $map->get($conversationUid));
        $this->assertArrayHasKey($expectedConversationPath, $map);
        $this->assertEquals($conversationUid, $map->get($expectedConversationPath));

        $this->assertArrayHasKey($scene1Uid, $map);
        $this->assertEquals($expectedScene1Path, $map->get($scene1Uid));
        $this->assertArrayHasKey($expectedScene1Path, $map);
        $this->assertEquals($scene1Uid, $map->get($expectedScene1Path));

        $this->assertArrayHasKey($turn1Uid, $map);
        $this->assertEquals($expectedTurn1Path, $map->get($turn1Uid));
        $this->assertArrayHasKey($expectedTurn1Path, $map);
        $this->assertEquals($turn1Uid, $map->get($expectedTurn1Path));

        $this->assertArrayHasKey($scene2Uid, $map);
        $this->assertEquals($expectedScene2Path, $map->get($scene2Uid));
        $this->assertArrayHasKey($expectedScene2Path, $map);
        $this->assertEquals($scene2Uid, $map->get($expectedScene2Path));

        $this->assertArrayHasKey($turn2Uid, $map);
        $this->assertEquals($expectedTurn2Path, $map->get($turn2Uid));
        $this->assertArrayHasKey($expectedTurn2Path, $map);
        $this->assertEquals($turn2Uid, $map->get($expectedTurn2Path));
    }
}
