<?php

namespace Tests\Feature;

use App\Console\Facades\ImportExportSerializer;
use App\ImportExportHelpers\ScenarioImportExportHelper;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Adapter\AbstractAdapter;
use OpenDialogAi\Core\Conversation\Behavior;
use OpenDialogAi\Core\Conversation\BehaviorsCollection;
use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\Core\Conversation\ConditionCollection;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\ConversationCollection;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\Facades\ScenarioDataClient;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\IntentCollection;
use OpenDialogAi\Core\Conversation\MessageTemplate;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\ScenarioCollection;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\SceneCollection;
use OpenDialogAi\Core\Conversation\Transition;
use OpenDialogAi\Core\Conversation\Turn;
use OpenDialogAi\Core\Conversation\TurnCollection;
use OpenDialogAi\Core\Conversation\VirtualIntent;
use Tests\TestCase;

/**
 * Class ImportExportScenariosTest
 *
 * @package Tests\Feature
 */
class ImportExportScenariosTest extends TestCase
{

    /**
     * @var Filesystem
     */
    protected $disk;
    /**
     * @var ConversationDataClient
     */
    protected $client;

    public function setUp(): void
    {
        parent::setUp();
        $this->setupFakeSpecificationDisk();
    }

    protected function setupFakeSpecificationDisk(): void
    {
        $this->disk = Storage::fake('specification');

        /** @var AbstractAdapter $diskAdapter */
        $diskAdapter = $this->disk->getAdapter();
        File::copyDirectory(base_path('tests/specification'), $diskAdapter->getPathPrefix());
    }

    /**
     * Adds Uid values to the provided Scenario and all its conversation objects.
     *
     * @param  Scenario  $scenario
     *
     * @return Scenario
     */
    public function addFakeUids(Scenario $scenario)
    {
        static $currentUid = 0;

        $getUid = fn (int $count) => "0x".(1000 + $count);
        $scenario->setUid($getUid($currentUid++));
        $conversations = $scenario->getConversations();
        foreach ($conversations as $conversation) {
            $conversation->setUid($getUid($currentUid++));
            foreach ($conversation->getScenes() as $scene) {
                $scene->setUid($getUid($currentUid++));
                foreach ($scene->getTurns() as $turn) {
                    $turn->setUid($getUid($currentUid++));
                    foreach ($turn->getRequestIntents() as $intent) {
                        $intent->setUid($getUid($currentUid++));
                    }
                    foreach ($turn->getResponseIntents() as $intent) {
                        $intent->setUid($getUid($currentUid++));
                    }
                }
            }
        }
        return $scenario;
    }

    /**
     * Returns a test scenario with conversations,scenes,turns and intents
     *
     * @return Scenario
     */
    public function getFullTestScenario(): Scenario
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

    /**
     * Returns a test scenario containing only scenario data, (no conversations)
     *
     * @return Scenario
     */
    public function getSimpleTestScenario()
    {
        $scenario = new Scenario();
        $scenario->setOdId("simple_scenario");
        $scenario->setName("Test scenario (Simple)");
        $scenario->setDescription("Test scenario description.");
        $scenario->setInterpreter("interpreter.core.nlp");
        $scenario->setActive(true);
        $scenario->setStatus(Scenario::LIVE_STATUS);
        return $scenario;
    }


    /**
     * Returns a Scenario matching the exported scenario specification/scenarios/example_scenario.scenario.json
     *
     * @param bool $withPathSubstitutableObjects
     * @return Scenario
     */
    public function getMatchingExampleScenario(bool $withPathSubstitutableObjects = true): Scenario
    {
        $scenario = new Scenario();
        $scenario->setOdId("example_scenario");
        $scenario->setName("Example scenario");
        $scenario->setDescription("An example scenario");

        $conversation = new Conversation($scenario);
        $conversation->setOdId("example_conversation");
        $conversation->setName("Example conversation");
        $conversation->setDescription("An example conversation");
        $conversation->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::STARTING_BEHAVIOR)]));

        $scenario->addConversation($conversation);

        $scene = new Scene($conversation);
        $scene->setOdId("example_scene");
        $scene->setName("Example scene");
        $scene->setDescription("An example scene");
        $scene->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::STARTING_BEHAVIOR)]));

        $conversation->addScene($scene);

        $turn = new Turn($scene);
        $turn->setOdId("example_turn");
        $turn->setName("Example turn");
        $turn->setDescription("An example turn");
        $turn->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::STARTING_BEHAVIOR)]));
        $turn->setValidOrigins(["example_origin"]);

        $scene->addTurn($turn);

        $requestIntent = new Intent($turn);
        $requestIntent->setOdId("example_request_intent");
        $requestIntent->setName("Example request intent");
        $requestIntent->setDescription("An example request intent");
        $requestIntent->setInterpreter("interpreter.core.example");
        $requestIntent->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::STARTING_BEHAVIOR)]));
        $requestIntent->setSampleUtterance("Example sample utterance");
        $requestIntent->setConfidence(1.0);
        $requestIntent->setSpeaker(Intent::USER);
        $requestIntent->setListensFor(["other_example_intent_id"]);

        $turn->addRequestIntent($requestIntent);

        $responseIntent = new Intent($turn);
        $responseIntent->setOdId("example_response_intent");
        $responseIntent->setName("Example response intent");
        $responseIntent->setDescription("An example response intent");
        $responseIntent->setInterpreter("interpreter.core.example");
        $responseIntent->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::COMPLETING_BEHAVIOR)]));
        $responseIntent->setSampleUtterance("Example sample utterance");
        $responseIntent->setConfidence(1);
        $responseIntent->setVirtualIntent(new VirtualIntent(Intent::USER, 'intent.app.test'));
        $responseIntent->setSpeaker(Intent::APP);

        $messageTemplate = new MessageTemplate();
        $messageTemplate->setName('message template');
        $messageTemplate->setOdId('message_template');
        $messageTemplate->setMessageMarkup('message markup');
        $messageTemplate->setDescription("description");

        $responseIntent->addMessageTemplate($messageTemplate);

        $turn->addResponseIntent($responseIntent);

        $this->addFakeUids($scenario);

        if ($withPathSubstitutableObjects) {
            $scenario->setConditions(new ConditionCollection([
                new Condition('eq', ['attribute' => 'selected_scenario'], ['value' => $scenario->getUid()])
            ]));

            $requestIntent->setTransition(new Transition(null, null, null));
            $responseIntent->setTransition(new Transition($conversation->getUid(), $scene->getUid(), null));
        }

        return $scenario;
    }

    /**
     * Returns a Scenario matching the exported scenario specification/scenarios/minimal_scenario.scenario.json
     *
     * @return Scenario
     */
    public function getMatchingMinimalScenario(): Scenario
    {
        $scenario = new Scenario();
        $scenario->setOdId("minimal_scenario");
        $scenario->setName("Minimal scenario");
        $scenario->setDescription("A minimal scenario");

        return $this->addFakeUids($scenario);
    }

    public function testExportSingleScenario()
    {
        $scenario = $this->getFullTestScenario();

        // Mock storing the scenario in DGraph
        ScenarioDataClient::shouldReceive('addFullScenarioGraph')->with($scenario)
            ->andReturn($this->addFakeUids($scenario));
        $storedScenario = ScenarioDataClient::addFullScenarioGraph($scenario);

        $expectedFilePath = ScenarioImportExportHelper::getScenarioFilePath($storedScenario->getOdId());

        // Mocks for pulling data from DGraph
        ConversationDataClient::shouldReceive('getAllScenarios')->andReturn(new ScenarioCollection([$storedScenario]));
        ScenarioDataClient::shouldReceive('getFullScenarioGraph')->with($storedScenario->getUid())
            ->andReturn($storedScenario);

        // Do the export
        $this->artisan('scenarios:export');

        $this->disk->assertExists($expectedFilePath);
        $data = $this->disk->get($expectedFilePath);
        $decodedData = json_decode($data, JSON_THROW_ON_ERROR);
        $this->assertIsArray($decodedData);
    }

    public function testExportMultipleScenarios()
    {
        // Store Scenario A (Storage mocked)
        $scenarioA = $this->getSimpleTestScenario();
        $scenarioA->setOdId("test_scenario_a");
        $scenarioA->setName("Test scenario (A)");
        ScenarioDataClient::shouldReceive('addFullScenarioGraph')->with($scenarioA)
            ->andReturn($this->addFakeUids($scenarioA));
        $storedScenarioA = ScenarioDataClient::addFullScenarioGraph($scenarioA);

        // Store Scenario B (Storage mocked)

        $scenarioB = $this->getSimpleTestScenario();
        $scenarioB->setOdId("test_scenario_b");
        $scenarioB->setName("Test scenario (B)");
        ScenarioDataClient::shouldReceive('addFullScenarioGraph')->with($scenarioB)
            ->andReturn($this->addFakeUids($scenarioB));
        $storedScenarioB = ScenarioDataClient::addFullScenarioGraph($scenarioB);

        // Store Scenario C (Storage mocked)
        $scenarioC = $this->getSimpleTestScenario();
        $scenarioC->setOdId("test_scenario_c");
        $scenarioC->setName("Test scenario (C)");
        ScenarioDataClient::shouldReceive('addFullScenarioGraph')->with($scenarioC)
            ->andReturn($this->addFakeUids($scenarioC));
        $storedScenarioC = ScenarioDataClient::addFullScenarioGraph($scenarioC);

        // Run the export (Storage mocked)
        ConversationDataClient::shouldReceive('getAllScenarios')->andReturn(new ScenarioCollection([
            $storedScenarioA,
            $storedScenarioB,
            $storedScenarioC
        ]));
        ScenarioDataClient::shouldReceive('getFullScenarioGraph')->withAnyArgs()
            ->andReturn($storedScenarioA, $storedScenarioB, $storedScenarioC);
        $this->artisan('scenarios:export');

        foreach ([
                     $storedScenarioA,
                     $storedScenarioB,
                     $storedScenarioC
                 ] as $storedScenario) {
            $filePath = ScenarioImportExportHelper::getScenarioFilePath($storedScenario->getOdId());
            $this->disk->assertExists($filePath);
        }
    }

    public function testExportOverwriteExistingFile()
    {
        $existingExportFilePath = ScenarioImportExportHelper::getScenarioFilePath("example_scenario");
        $this->disk->assertExists($existingExportFilePath);
        $previousData = ScenarioImportExportHelper::getScenarioFileData($existingExportFilePath);

        $testScenario = new Scenario();
        $testScenario->setOdId("example_scenario");
        $testScenario->setName("Other example scenario");
        ScenarioDataClient::shouldReceive('addFullScenarioGraph')->with($testScenario)
            ->andReturn($this->addFakeUids($testScenario));
        $storedTestScenario = ScenarioDataClient::addFullScenarioGraph($testScenario);

        ConversationDataClient::shouldReceive('getAllScenarios')->andReturn(new ScenarioCollection([$storedTestScenario]));
        ScenarioDataClient::shouldReceive('getFullScenarioGraph')->withAnyArgs()->andReturn($storedTestScenario);
        $this->artisan('scenarios:export')->expectsOutput(sprintf(
            "Scenario file at %s already exists. Deleting...",
            $existingExportFilePath
        ));

        $newData = ScenarioImportExportHelper::getScenarioFileData($existingExportFilePath);
        $this->assertNotEquals($previousData, $newData);
    }

    public function testImportAllScenarios()
    {
        $exportedScenarios = ScenarioImportExportHelper::getScenarioFiles();
        $this->assertCount(2, $exportedScenarios);

        $exampleScenarioFilePath = ScenarioImportExportHelper::getScenarioFilePath("example_scenario");
        $minimalScenarioFilePath = ScenarioImportExportHelper::getScenarioFilePath("minimal_scenario");

        // Run the Import (Storage mocked)
        $storedExampleScenario = $this->getMatchingExampleScenario(false);
        $storedMinimalScenario = $this->getMatchingMinimalScenario();

        ConversationDataClient::shouldReceive('getAllScenarios')
            ->twice()
            ->andReturn(new ScenarioCollection(), new ScenarioCollection([$storedExampleScenario]));

        ScenarioDataClient::shouldReceive('addFullScenarioGraph')
            ->twice()
            ->andReturn($storedExampleScenario, $storedMinimalScenario);

        // To update the path in the scenario's condition
        ConversationDataClient::shouldReceive('updateScenario')
            ->once();

        // To update the path in the intent's transition
        ConversationDataClient::shouldReceive('updateIntent')
            ->once();

        // After updates are made we get the full scenario with the updates included now
        ScenarioDataClient::shouldReceive('getFullScenarioGraph')
            ->times(3)
            ->andReturn($this->getMatchingExampleScenario());

        $this->artisan('scenarios:import')
            ->expectsOutput(sprintf("Importing scenario from file %s...", $exampleScenarioFilePath))
            ->expectsOutput(sprintf("Importing scenario from file %s...", $minimalScenarioFilePath));

        // Check stored scenarios (Storage mocked)
        $allStoredScenarios = new ScenarioCollection([
            $storedExampleScenario,
            $storedMinimalScenario
        ]);

        ConversationDataClient::shouldReceive('getAllScenarios')
            ->once()
            ->andReturn($allStoredScenarios);

        $storedScenarios = ConversationDataClient::getAllScenarios(false);
        $this->assertCount(2, $storedScenarios);

        $storedExampleScenario = $storedScenarios->filter(fn ($scenario) => $scenario->getOdId() === "example_scenario");
        $this->assertNotNull($storedExampleScenario);

        $storedMinimalScenario = $storedScenarios->filter(fn ($scenario) => $scenario->getOdId() === "minimal_scenario");
        $this->assertNotNull($storedMinimalScenario);
    }

    public function testImportInvalidScenario()
    {
        // Invalid scenario JSON. missing closing '}'
        $invalidScenarioJson = '{ "od_id": "invalid_json", "name": "Invalid_json"  ';

        // Stored invalid Scenario
        $filePath = ScenarioImportExportHelper::getScenarioFilePath("invalid_json");
        $this->disk->put($filePath, $invalidScenarioJson);

        // Run import, mocking data for example_scenario.scenario.json and minimal_scenario.scenario.json
        $storedExampleScenario = $this->getMatchingExampleScenario();
        $storedMinimalScenario = $this->getMatchingMinimalScenario();

        ConversationDataClient::shouldReceive('getAllScenarios')
            ->twice()
            ->andReturn(new ScenarioCollection(), new ScenarioCollection([$storedExampleScenario]));

        ScenarioDataClient::shouldReceive('addFullScenarioGraph')
            ->twice()
            ->andReturn($storedExampleScenario, $storedMinimalScenario);

        // To update the path in the scenario's condition
        ConversationDataClient::shouldReceive('updateScenario')
            ->once();

        // To update the path in the intent's transition
        ConversationDataClient::shouldReceive('updateIntent')
            ->once();

        // After updates are made we get the full scenario with the updates included now
        ScenarioDataClient::shouldReceive('getFullScenarioGraph')
            ->times(3)
            ->andReturn($this->getMatchingExampleScenario());

        $this->artisan('scenarios:import')
            ->expectsOutput(sprintf("Import of %s failed. Unable to decode file as json", $filePath));
    }

    public function testImportDuplicateOdId()
    {
        // Setup an existing scenario with odId 'example_scenario'
        $existingExampleScenario = new Scenario();
        $existingExampleScenario->setOdId("example_scenario");
        $existingExampleScenario->setName("Existing scenario");
        $existingExampleScenario->setDescription("An existing scenario");
        ScenarioDataClient::shouldReceive('addFullScenarioGraph')->once()
            ->andReturn($this->addFakeUids($existingExampleScenario));
        $storedExistingExampleScenario = ScenarioDataClient::addFullScenarioGraph($existingExampleScenario);
        // We've added one scenario, expect one to exist.
        ConversationDataClient::shouldReceive('getAllScenarios')->once()->andReturn(new ScenarioCollection([$storedExistingExampleScenario]));
        $previousScenarios = ConversationDataClient::getAllScenarios(false);
        $this->assertCount(1, $previousScenarios);

        // Run the Import (Storage mocked)
        $storedMinimalScenario = $this->getMatchingMinimalScenario();

        ConversationDataClient::shouldReceive('getAllScenarios')
            ->twice()
            ->andReturn(new ScenarioCollection([$storedExistingExampleScenario]));

        ScenarioDataClient::shouldReceive('addFullScenarioGraph')
            ->once()
            ->andReturn($storedMinimalScenario);

        ScenarioDataClient::shouldReceive('getFullScenarioGraph')
            ->once()
            ->andReturn($storedMinimalScenario);

        $this->artisan('scenarios:import')
            ->expectsOutput(sprintf(
                "An existing Scenario with odId %s already exists!. Skipping %s!",
                "example_scenario",
                ScenarioImportExportHelper::getScenarioFilePath("example_scenario")
            ));

        // We should have added a second scenario.
        ConversationDataClient::shouldReceive('getAllScenarios')->once()->andReturn(new ScenarioCollection([
            $storedExistingExampleScenario,
            $storedMinimalScenario
        ]));
        $currentScenarios = ConversationDataClient::getAllScenarios(false);
        $this->assertCount(2, $currentScenarios);

        // The example scenario import was skipped, so it should be unchanged.
        $currentExampleScenarioUid =
            $currentScenarios->filter(fn ($scenario) => $scenario->getOdId() === "example_scenario")->first()->getUid();
        ScenarioDataClient::shouldReceive('getFullScenarioGraph')->once()->andReturn($storedExistingExampleScenario);
        $currentStoredExampleScenario = ScenarioDataClient::getFullScenarioGraph($currentExampleScenarioUid);
        $this->assertEquals($storedExistingExampleScenario, $currentStoredExampleScenario);
    }

    public function testImportExportRoundTrip()
    {
        // Get data for existing scenario files.
        $previousScenarioFiles = ScenarioImportExportHelper::getScenarioFiles();
        $previousScenarioFileData = array_combine(
            $previousScenarioFiles,
            array_map(fn ($path) => ScenarioImportExportHelper::getScenarioFileData($path), $previousScenarioFiles)
        );

        // Round trip
        $storedExampleScenario = $this->getMatchingExampleScenario();
        $storedMinimalScenario = $this->getMatchingMinimalScenario();

        ConversationDataClient::shouldReceive('getAllScenarios')
            ->twice()
            ->andReturn(
                new ScenarioCollection(),
                new ScenarioCollection([$storedExampleScenario])
            );

        ScenarioDataClient::shouldReceive('addFullScenarioGraph')
            ->twice()
            ->andReturn($storedExampleScenario, $storedMinimalScenario);

        // To update the path in the scenario's condition
        ConversationDataClient::shouldReceive('updateScenario')
            ->once();

        // To update the path in the intent's transition
        ConversationDataClient::shouldReceive('updateIntent')
            ->once();

        // After updates are made we get the full scenario with the updates included now
        ScenarioDataClient::shouldReceive('getFullScenarioGraph')
            ->times(3)
            ->andReturn($this->getMatchingExampleScenario());

        $this->artisan('scenarios:import');

        ConversationDataclient::shouldReceive('getAllScenarios')
            ->once()
            ->andReturn(new ScenarioCollection([$storedExampleScenario, $storedMinimalScenario]));

        ScenarioDataClient::shouldReceive('getFullScenarioGraph')
            ->twice()
            ->andReturn($storedExampleScenario, $storedMinimalScenario);

        $this->artisan('scenarios:export');

        // Get data for current scenario files.
        $currentScenarioFiles = ScenarioImportExportHelper::getScenarioFiles();
        $currentScenarioFilesData = array_combine(
            $currentScenarioFiles,
            array_map(fn ($path) => ScenarioImportExportHelper::getScenarioFileData($path), $currentScenarioFiles)
        );

        $this->assertEquals(count($previousScenarioFileData), count($currentScenarioFilesData));

        foreach ($currentScenarioFilesData as $filePath => $currentData) {
            $this->assertArrayHasKey($filePath, $previousScenarioFileData);
            $previousData = $previousScenarioFileData[$filePath];
            $this->assertEquals(
                ImportExportSerializer::decode($previousData, 'json'),
                ImportExportSerializer::decode($currentData, 'json')
            );
        }
    }
}
