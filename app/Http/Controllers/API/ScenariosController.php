<?php


namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Facades\Serializer;
use App\Http\Requests\ConversationObjectDuplicationRequest;
use App\Http\Requests\ConversationRequest;
use App\Http\Requests\ScenarioRequest;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\ScenarioResource;
use App\Rules\OdId;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use OpenDialogAi\Core\Console\Commands\CreateCoreConfigurations;
use OpenDialogAi\Core\Conversation\Behavior;
use OpenDialogAi\Core\Conversation\BehaviorsCollection;
use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\Core\Conversation\ConditionCollection;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\MessageTemplate;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\Transition;
use OpenDialogAi\Core\Conversation\Turn;
use OpenDialogAi\MessageBuilder\MessageMarkUpGenerator;

class ScenariosController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Returns a collection of scenarios.
     *
     * @return ScenarioResource
     */
    public function index(): ScenarioResource
    {
        $scenarios = ConversationDataClient::getAllScenarios();
        return new ScenarioResource($scenarios);
    }

    /**
     * Display the specified scenario.
     *
     * @param Scenario $scenario
     * @return ScenarioResource
     */
    public function show(Scenario $scenario): ScenarioResource
    {
        return new ScenarioResource($scenario);
    }


    /**
     * Returns a collection of conversations for a particular scenario.
     *
     * @param Scenario $scenario
     * @return ConversationResource
     */
    public function showConversationsByScenario(Scenario $scenario): ConversationResource
    {
        $conversations = ConversationDataClient::getAllConversationsByScenario($scenario);
        return new ConversationResource($conversations);
    }

    /**
     * Store a newly created conversation against a particular scenario.
     *
     * @param Scenario $scenario
     * @param ConversationRequest $request
     * @return ConversationResource
     */
    public function storeConversationsAgainstScenario(Scenario $scenario, ConversationRequest $request): ConversationResource
    {
        $newConversation = Serializer::deserialize($request->getContent(), Conversation::class, 'json');
        $newConversation->setScenario($scenario);
        $conversation = ConversationDataClient::addConversation($newConversation);

        return new ConversationResource($conversation);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ScenarioRequest $request
     * @return JsonResponse
     */
    public function store(ScenarioRequest $request): JsonResponse
    {
        /** @var Scenario $newScenario */
        $newScenario = Serializer::deserialize($request->getContent(), Scenario::class, 'json');

        if ($newScenario->getInterpreter() === "") {
            $newScenario->setInterpreter(CreateCoreConfigurations::DEFAULT_CALLBACK);
        }

        $persistedScenario = $this->createDefaultConversations($newScenario);

        // Add a new condition to the scenario now that it has an ID
        $persistedScenario = $this->setDefaultScenarioCondition($persistedScenario);
        $updatedScenario = ConversationDataClient::updateScenario($persistedScenario);

        return (new ScenarioResource($updatedScenario))->response()->setStatusCode(201);
    }

    /**
     * @param Scenario $scenario
     * @return Scenario
     */
    private function createDefaultConversations(Scenario $scenario): Scenario
    {
        $scenarioName = $scenario->getName();
        $scenarioNameAsId = preg_replace('/\s/', '', ucwords($scenario->getOdId()));
        $welcomeOutgoingIntentId = "intent.app.welcomeResponseFor$scenarioNameAsId";
        $noMatchOutgoingIntentId = "intent.app.noMatchResponse$scenarioNameAsId";

        $welcomeName = 'Welcome';
        $welcomeConversation = $this->createAtomicCallbackConversation(
            $scenario,
            $welcomeName,
            'intent.core.welcome',
            'Hello from user',
            $welcomeOutgoingIntentId,
            "Hi! This is the default welcome message for the $scenarioName Scenario.",
            true
        );

        $triggerName = 'Trigger';
        $triggerIntent = $this->createRequestOnlyCallbackConversation(
            $scenario,
            $triggerName,
            'intent.core.welcome',
            'Hello from user',
            Intent::USER
        );

        $noMatchConversation = $this->createAtomicCallbackConversation(
            $scenario,
            'No Match',
            'intent.core.NoMatch',
            '[no match]',
            $noMatchOutgoingIntentId,
            'Sorry, I didn\'t understand that'
        );

        $scenario->addConversation($welcomeConversation);
        $scenario->addConversation($triggerIntent->getConversation());
        $scenario->addConversation($noMatchConversation);

        $scenario = ConversationDataClient::addFullScenarioGraph($scenario);

        $welcomeId = $this->convertNameToId($welcomeName);
        $triggerId = $this->convertNameToId($triggerName);

        $welcomeIntent = $this->getRequestIntentForRequestOnlyConversation($scenario, $welcomeId);
        $triggerIntent = $this->getRequestIntentForRequestOnlyConversation($scenario, $triggerId);

        $triggerIntent->setTransition(new Transition(
            $welcomeIntent->getConversation()->getUid(),
            $welcomeIntent->getScene()->getUid(),
            $welcomeIntent->getTurn()->getUid()
        ));

        ConversationDataClient::updateIntent($triggerIntent);

        return $scenario;
    }

    /**
     * Update the specified scenario.
     *
     * @param ScenarioRequest $request
     * @param Scenario $scenario
     * @return ScenarioResource
     */
    public function update(ScenarioRequest $request, Scenario $scenario): ScenarioResource
    {
        $scenarioUpdate = Serializer::deserialize($request->getContent(), Scenario::class, 'json');
        $updatedScenario = ConversationDataClient::updateScenario($scenarioUpdate);
        return new ScenarioResource($updatedScenario);
    }

    /**
     * Destroy the specified scenario.
     *
     * @param Scenario $scenario
     * @return Response $response
     */
    public function destroy(Scenario $scenario): Response
    {
        if (ConversationDataClient::deleteScenarioByUid($scenario->getUid())) {
            return response()->noContent(200);
        } else {
            return response('Error deleting scenario, check the logs', 500);
        }
    }

    /**
     * @param ConversationObjectDuplicationRequest $request
     * @param Scenario $scenario
     * @return ScenarioResource
     */
    public function duplicate(ConversationObjectDuplicationRequest $request, Scenario $scenario): ScenarioResource
    {
        $scenario = ConversationDataClient::getFullScenarioGraph($scenario->getUid());

        $odId = $request->get('od_id', sprintf("%s_copy", $scenario->getOdId()));

        if (OdId::isOdIdUniqueWithinParentScope($odId)) {
            $name = $request->get('name', sprintf("%s copy", $scenario->getName()));
        } else {
            $originalOdId = $odId;
            $i = 1;

            do {
                $i++;
                $odId = sprintf("%s_%d", $originalOdId, $i);
            } while (!OdId::isOdIdUniqueWithinParentScope($odId));

            $name = $request->get('name', sprintf("%s copy %d", $scenario->getName(), $i));
        }

        $scenario->removeUid();
        $scenario->setOdId($odId);
        $scenario->setName($name);

        $duplicate = ConversationDataClient::addFullScenarioGraph($scenario);

        // Reset the default condition as it will have the old UID
        $duplicate = $this->setDefaultScenarioCondition($duplicate);
        $duplicate = ConversationDataClient::updateScenario($duplicate);

        return new ScenarioResource($duplicate);
    }

    /**
     * @param Scenario $scenario
     * @param string $name
     * @param string $incomingIntentId
     * @param string $incomingSampleUtterance
     * @param string $outgoingIntentId
     * @param string $outgoingSampleUtterance
     * @param bool $botLed = false
     * @return Conversation
     */
    private function createAtomicCallbackConversation(
        Scenario $scenario,
        string $name,
        string $incomingIntentId,
        string $incomingSampleUtterance,
        string $outgoingIntentId,
        string $outgoingSampleUtterance,
        bool $botLed = false
    ): Conversation {
        $nameAsId = $this->convertNameToId($name);

        $turn = $this->createConversationToTurn($scenario, $name, $nameAsId);

        $incomingIntent = new Intent($turn, Intent::USER);
        $incomingIntent->setIsRequestIntent(!$botLed);
        $incomingIntent->setName($incomingIntentId);
        $incomingIntent->setOdId($incomingIntentId);
        $incomingIntent->setDescription('Automatically generated');
        $incomingIntent->setSampleUtterance($incomingSampleUtterance);
        $incomingIntent->setInterpreter(CreateCoreConfigurations::DEFAULT_CALLBACK);
        $incomingIntent->setConfidence(1);
        $incomingIntent->setCreatedAt(Carbon::now());
        $incomingIntent->setUpdatedAt(Carbon::now());

        $outgoingIntent = new Intent($turn, Intent::APP);
        $outgoingIntent->setIsRequestIntent($botLed);
        $outgoingIntent->setName($outgoingIntentId);
        $outgoingIntent->setOdId($outgoingIntentId);
        $outgoingIntent->setDescription('Automatically generated');
        $outgoingIntent->setSampleUtterance($outgoingSampleUtterance);
        $outgoingIntent->setConfidence(1);

        if (!$botLed) {
            $outgoingIntent->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::COMPLETING_BEHAVIOR)]));
        }

        $outgoingIntent->setCreatedAt(Carbon::now());
        $outgoingIntent->setUpdatedAt(Carbon::now());

        if ($botLed) {
            $turn->addRequestIntent($outgoingIntent);
            $turn->addResponseIntent($incomingIntent);
        } else {
            $turn->addRequestIntent($incomingIntent);
            $turn->addResponseIntent($outgoingIntent);
        }

        $messageTemplate = new MessageTemplate();
        $messageTemplate->setName('auto generated');
        $messageTemplate->setOdId('auto_generated');
        $messageTemplate->setMessageMarkup((new MessageMarkUpGenerator())->addTextMessage($outgoingSampleUtterance)->getMarkUp());

        $outgoingIntent->addMessageTemplate($messageTemplate);

        return $turn->getConversation();
    }

    /**
     * @param Scenario $scenario
     * @param string $name
     * @param string $intentId
     * @param string $sampleUtterance
     * @param string $speaker
     * @return Intent
     */
    private function createRequestOnlyCallbackConversation(
        Scenario $scenario,
        string $name,
        string $intentId,
        string $sampleUtterance,
        string $speaker
    ): Intent {
        $nameAsId = $this->convertNameToId($name);

        $turn = $this->createConversationToTurn($scenario, $name, $nameAsId);

        $requestIntent = new Intent($turn, $speaker);
        $requestIntent->setIsRequestIntent(true);
        $requestIntent->setName($intentId);
        $requestIntent->setOdId($intentId);
        $requestIntent->setDescription('Automatically generated');
        $requestIntent->setSampleUtterance($sampleUtterance);
        $requestIntent->setConfidence(1);

        if ($speaker === Intent::USER) {
            $requestIntent->setInterpreter(CreateCoreConfigurations::DEFAULT_CALLBACK);
        } else {
            $requestIntent->setInterpreter('');
        }

        $turn->addRequestIntent($requestIntent);

        if ($speaker === Intent::APP) {
            $messageTemplate = new MessageTemplate();
            $messageTemplate->setName('auto generated');
            $messageTemplate->setOdId('auto_generated');
            $messageTemplate->setMessageMarkup((new MessageMarkUpGenerator())->addTextMessage($sampleUtterance)->getMarkUp());

            $requestIntent->addMessageTemplate($messageTemplate);
        }

        return $requestIntent;
    }

    /**
     * @param Scenario $scenario
     * @param string $name
     * @param $nameAsId
     * @return Turn
     */
    private function createConversationToTurn(Scenario $scenario, string $name, $nameAsId): Turn
    {
        $conversation = new Conversation($scenario);
        $conversation->setName("$name Conversation");
        $conversation->setOdId(sprintf('%s_conversation', $nameAsId));
        $conversation->setDescription('Automatically generated');
        $conversation->setInterpreter('');
        $conversation->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::STARTING_BEHAVIOR)]));
        $conversation->setCreatedAt(Carbon::now());
        $conversation->setUpdatedAt(Carbon::now());

        $scene = new Scene($conversation);
        $scene->setName("$name Scene");
        $scene->setOdId(sprintf('%s_scene', $nameAsId));
        $scene->setDescription('Automatically generated');
        $scene->setInterpreter('');
        $scene->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::STARTING_BEHAVIOR)]));
        $scene->setCreatedAt(Carbon::now());
        $scene->setUpdatedAt(Carbon::now());

        $turn = new Turn($scene);
        $turn->setName("$name Turn");
        $turn->setOdId(sprintf('%s_turn', $nameAsId));
        $turn->setDescription('Automatically generated');
        $turn->setInterpreter('');
        $turn->setBehaviors(new BehaviorsCollection([
            new Behavior(Behavior::STARTING_BEHAVIOR),
            new Behavior(Behavior::OPEN_BEHAVIOR),
        ]));
        $turn->setCreatedAt(Carbon::now());
        $turn->setUpdatedAt(Carbon::now());

        $scene->addTurn($turn);
        $conversation->addScene($scene);

        return $turn;
    }

    /**
     * @param string $name
     * @return string
     */
    private function convertNameToId(string $name)
    {
        return preg_replace('/\s/', '_', strtolower($name));
    }

    /**
     * @param Scenario $scenario
     * @param string $id
     * @return Intent
     */
    private function getRequestIntentForRequestOnlyConversation(Scenario $scenario, string $id): Intent
    {
        /** @var Conversation $conversation */
        $conversation = $scenario->getConversations()->getObjectsWithId(sprintf('%s_conversation', $id))->first();

        /** @var Scene $scene */
        $scene = $conversation->getScenes()->first();

        /** @var Turn $turn */
        $turn = $scene->getTurns()->first();

        /** @var Intent */
        return $turn->getRequestIntents()->first();
    }

    /**
     * @param Scenario $scenario
     * @return Scenario
     */
    private function setDefaultScenarioCondition(Scenario $scenario): Scenario
    {
        $condition = new Condition(
            'eq',
            ['attribute' => 'user.selected_scenario'],
            ['value' => $scenario->getUid()]
        );

        $scenario->setConditions(new ConditionCollection([$condition]));

        return $scenario;
    }
}
