<?php

namespace Tests\Feature;

use App\Console\Facades\ImportExportSerializer;
use App\ImportExportHelpers\ScenarioImportExportHelper;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Adapter\AbstractAdapter;
use OpenDialogAi\Core\Conversation\Behavior;
use OpenDialogAi\Core\Conversation\BehaviorsCollection;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\ConversationCollection;
use OpenDialogAi\Core\Conversation\DataClients\ConversationDataClient;
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
use OpenDialogAi\GraphQLClient\GraphQLClientInterface;
use Tests\TestCase;

/**
 * Class ImportExportScenariosTest
 *
 * @package Tests\Feature
 */
class ImportExportScenariosTest extends TestCase
{

    /**
     * @var \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected $disk;
    /**
     * @var ConversationDataClient
     */
    protected $client;

    public function resetGraphQL()
    {
        $client = resolve(GraphQLClientInterface::class);
        $client->dropAll();
        $client->setSchema(config('opendialog.graphql.schema'));
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->setupFakeSpecificationDisk();
        $this->resetGraphQL();

        $this->client = resolve(ConversationDataClient::class);
    }

    protected function setupFakeSpecificationDisk(): void
    {
        Artisan::call('schema:init', [
                '--yes' => true
            ]);

        $this->disk = Storage::fake('specification');

        /** @var AbstractAdapter $diskAdapter */
        $diskAdapter = $this->disk->getAdapter();
        File::copyDirectory(base_path('tests/specification'), $diskAdapter->getPathPrefix());
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

    public function testExportSingleScenario()
    {
        $scenario = $this->getFullTestScenario();
        $storedScenario = $this->client->addFullScenarioGraph($scenario);

        $expectedFilePath = ScenarioImportExportHelper::getScenarioFilePath($storedScenario->getOdId());
        $this->artisan('scenarios:export');
        $this->disk->assertExists($expectedFilePath);

        $data = $this->disk->get($expectedFilePath);
        $decodedData = json_decode($data, JSON_THROW_ON_ERROR);
        $this->assertIsArray($decodedData);
    }

    public function testExportMultipleScenarios()
    {
        $scenarioA = $this->getFullTestScenario();
        $scenarioA->setOdId("test_scenario_a");
        $scenarioA->setName("Test scenario (A)");
        $storedScenarioA = $this->client->addFullScenarioGraph($scenarioA);

        $scenarioB = $this->getFullTestScenario();
        $scenarioB->setOdId("test_scenario_b");
        $scenarioB->setName("Test scenario (B)");
        $storedScenarioB = $this->client->addFullScenarioGraph($scenarioB);

        $scenarioC = $this->getFullTestScenario();
        $scenarioC->setOdId("test_scenario_c");
        $scenarioC->setName("Test scenario (C)");
        $storedScenarioC = $this->client->addFullScenarioGraph($scenarioC);

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
        $storedTestScenario = $this->client->addFullScenarioGraph($testScenario);

        $this->artisan('scenarios:export')->expectsOutput(sprintf("Scenario file at %s already exists. Deleting...",
            $existingExportFilePath));

        $newData = ScenarioImportExportHelper::getScenarioFileData($existingExportFilePath);
        $this->assertNotEquals($previousData, $newData);
    }

    public function testImportAllScenarios()
    {
        $existingScenarios = $this->client->getAllScenarios(false);
        $this->assertCount(0, $existingScenarios);

        $exportedScenarios = ScenarioImportExportHelper::getScenarioFiles();
        $this->assertCount(2, $exportedScenarios);

        $exampleScenarioFilePath = ScenarioImportExportHelper::getScenarioFilePath("example_scenario");
        $minimalScenarioFilePath = ScenarioImportExportHelper::getScenarioFilePath("minimal_scenario");

        $this->artisan('scenarios:import')->expectsOutput(sprintf("Importing scenario from file %s...", $exampleScenarioFilePath))
            ->expectsOutput(sprintf("Importing scenario from file %s...", $minimalScenarioFilePath));

        $storedScenarios = $this->client->getAllScenarios(false);
        $this->assertCount(2, $storedScenarios);

        $storedExampleScenario = $storedScenarios->filter(fn($scenario) => $scenario->getOdId() === "example_scenario");
        $this->assertNotNull($storedExampleScenario);

        $storedMinimalScenario = $storedScenarios->filter(fn($scenario) => $scenario->getOdId() === "minimal_scenario");
        $this->assertNotNull($storedMinimalScenario);

    }

    public function testImportInvalidScenario()
    {
        // Invalid scenario JSON. missing closing '}'
        $invalidScenarioJson = '{ "od_id": "invalid_json", "name": "Invalid_json"  ';

        // Stored invalid Scenario
        $filePath = ScenarioImportExportHelper::getScenarioFilePath("invalid_json");
        $this->disk->put($filePath, $invalidScenarioJson);

        $this->artisan('scenarios:import')->expectsOutput(sprintf("Import of %s failed. Unable to decode file as json",
            $filePath));
    }

    public function testImportDuplicateOdId()
    {
        // Setup an existing scenario with odId 'example_scenario'
        $existingExampleScenario = new Scenario();
        $existingExampleScenario->setOdId("example_scenario");
        $existingExampleScenario->setName("Existing scenario");
        $existingExampleScenario->setDescription("An existing scenario");
        $storedExistingExampleScenario = $this->client->addFullScenarioGraph($existingExampleScenario);
        // We've added one scenario, expect one to exist.
        $previousScenarios = $this->client->getAllScenarios(false);
        $this->assertCount(1, $previousScenarios);

        $this->artisan('scenarios:import')
            ->expectsOutput(sprintf("An existing Scenario with odId %s already exists!. Skipping %s!", "example_scenario",
                ScenarioImportExportHelper::getScenarioFilePath("example_scenario")));

        // We should have added a second scenario.
        $currentScenarios = $this->client->getAllScenarios(false);
        $this->assertCount(2, $currentScenarios);

        // The example scenario import was skipped, so it should be unchanged.
        $currentExampleScenarioUid =
            $currentScenarios->filter(fn($scenario) => $scenario->getOdId() === "example_scenario")->first()->getUid();
        $currentStoredExampleScenario = $this->client->getFullScenarioGraph($currentExampleScenarioUid);
        $this->assertEquals($storedExistingExampleScenario, $currentStoredExampleScenario);

    }

    public function testImportExportRoundTrip()
    {

        // Get data for existing scenario files.
        $previousScenarioFiles = ScenarioImportExportHelper::getScenarioFiles();
        $previousScenarioFileData = array_combine($previousScenarioFiles,
            array_map(fn($path) => ScenarioImportExportHelper::getScenarioFileData($path), $previousScenarioFiles));

        // Round trip
        $this->artisan('scenarios:import');
        $this->artisan('scenarios:export');

        // Get data for current scenario files.
        $currentScenarioFiles = ScenarioImportExportHelper::getScenarioFiles();
        $currentScenarioFilesData = array_combine($currentScenarioFiles,
            array_map(fn($path) => ScenarioImportExportHelper::getScenarioFileData($path), $currentScenarioFiles));

        $this->assertEquals(count($previousScenarioFileData), count($currentScenarioFilesData));

        foreach ($currentScenarioFilesData as $filePath => $currentData) {
            $this->assertArrayHasKey($filePath, $previousScenarioFileData);
            $previousData = $previousScenarioFileData[$filePath];
            $this->assertEquals(ImportExportSerializer::decode($currentData, 'json'),
                ImportExportSerializer::decode($previousData, 'json'));
        }

    }

}
