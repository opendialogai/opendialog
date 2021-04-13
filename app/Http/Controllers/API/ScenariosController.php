<?php


namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Facades\Serializer;
use App\Http\Requests\ConversationRequest;
use App\Http\Requests\ScenarioRequest;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\ScenarioResource;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use OpenDialogAi\Core\Conversation\Behavior;
use OpenDialogAi\Core\Conversation\BehaviorsCollection;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\Turn;
use OpenDialogAi\MessageBuilder\MessageMarkUpGenerator;
use OpenDialogAi\ResponseEngine\MessageTemplate;
use OpenDialogAi\ResponseEngine\OutgoingIntent;

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
        $scenarios = ConversationDataClient::getAllScenarios(false);
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
        $conversations = ConversationDataClient::getAllConversationsByScenario($scenario, false);
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

        $updatedScenario = $this->createDefaultConversations($newScenario);

        return (new ScenarioResource($updatedScenario))->response()->setStatusCode(201);
    }

    /**
     * @param Scenario $scenario
     * @return Scenario
     */
    private function createDefaultConversations(Scenario $scenario): Scenario
    {
        $welcomeConversation = $this->createAtomicCallbackConversation(
            $scenario,
            'Welcome',
            'intent.core.welcome',
            'Hello from user',
            'intent.app.welcomeResponse',
            'Hello from bot'
        );

        $noMatchConversation = $this->createAtomicCallbackConversation(
            $scenario,
            'No Match',
            'intent.core.NoMatch',
            '[no match]',
            'intent.app.noMatchResponse',
            'Sorry, I didn\'t understand that'
        );

        $scenario->addConversation($welcomeConversation);
        $scenario->addConversation($noMatchConversation);

        $persistedScenario = ConversationDataClient::addFullScenarioGraph($scenario);

        $this->createMessageForOutgoingIntent(
            'intent.app.welcomeResponse',
            "Hi! This is the default welcome message for the Welcome Scenario."
        );
        $this->createMessageForOutgoingIntent(
            'intent.app.noMatchResponse',
            "Sorry, I didn't understand that."
        );

        return $persistedScenario;
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
     * @param Scenario $scenario
     * @param string $name
     * @param string $incomingIntentId
     * @param string $incomingSampleUtterance
     * @param string $outgoingIntentId
     * @param string $outgoingSampleUtterance
     * @return Conversation
     */
    private function createAtomicCallbackConversation(
        Scenario $scenario,
        string $name,
        string $incomingIntentId,
        string $incomingSampleUtterance,
        string $outgoingIntentId,
        string $outgoingSampleUtterance
    ): Conversation {
        $nameAsId = preg_replace('/\s/', '_', strtolower($name));

        $conversation = new Conversation($scenario);
        $conversation->setName("$name Conversation");
        $conversation->setOdId(sprintf('%s_conversation', $nameAsId));
        $conversation->setDescription('Automatically generated');
        $conversation->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::STARTING_BEHAVIOR)]));
        $conversation->setCreatedAt(Carbon::now());
        $conversation->setUpdatedAt(Carbon::now());

        $scene = new Scene($conversation);
        $scene->setName("$name Scene");
        $scene->setOdId(sprintf('%s_scene', $nameAsId));
        $scene->setDescription('Automatically generated');
        $scene->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::STARTING_BEHAVIOR)]));
        $scene->setCreatedAt(Carbon::now());
        $scene->setUpdatedAt(Carbon::now());

        $turn = new Turn($scene);
        $turn->setName("$name Turn");
        $turn->setOdId(sprintf('%s_turn', $nameAsId));
        $turn->setDescription('Automatically generated');
        $turn->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::STARTING_BEHAVIOR)]));
        $turn->setCreatedAt(Carbon::now());
        $turn->setUpdatedAt(Carbon::now());

        $requestIntent = new Intent($turn, Intent::USER);
        $requestIntent->setIsRequestIntent(true);
        $requestIntent->setName($incomingIntentId);
        $requestIntent->setOdId($incomingIntentId);
        $requestIntent->setDescription('Automatically generated');
        $requestIntent->setSampleUtterance($incomingSampleUtterance);
        $requestIntent->setInterpreter('interpreter.core.callbackInterpreter');
        $requestIntent->setConfidence(1);
        $requestIntent->setCreatedAt(Carbon::now());
        $requestIntent->setUpdatedAt(Carbon::now());

        $responseIntent = new Intent($turn, Intent::APP);
        $responseIntent->setIsRequestIntent(false);
        $responseIntent->setName($outgoingIntentId);
        $responseIntent->setOdId($outgoingIntentId);
        $responseIntent->setDescription('Automatically generated');
        $responseIntent->setSampleUtterance($outgoingSampleUtterance);
        $responseIntent->setConfidence(1);
        $responseIntent->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::COMPLETING_BEHAVIOR)]));
        $responseIntent->setCreatedAt(Carbon::now());
        $responseIntent->setUpdatedAt(Carbon::now());

        $turn->addRequestIntent($requestIntent);
        $turn->addResponseIntent($responseIntent);
        $scene->addTurn($turn);
        $conversation->addScene($scene);

        return $conversation;
    }

    /**
     * @param string $outgoingIntentId
     * @param string $text
     */
    private function createMessageForOutgoingIntent(string $outgoingIntentId, string $text): void
    {
        /** @var OutgoingIntent $outgoingIntent */
        $outgoingIntent = OutgoingIntent::firstOrCreate(['name' => $outgoingIntentId]);

        if ($outgoingIntent->messageTemplates()->get()->isEmpty()) {
            MessageTemplate::firstOrCreate([
                'name' => $outgoingIntentId,
            ], [
                'message_markup' => (new MessageMarkUpGenerator())->addTextMessage($text)->getMarkUp(),
                'outgoing_intent_id' => $outgoingIntent->id
            ]);
        }
    }
}
