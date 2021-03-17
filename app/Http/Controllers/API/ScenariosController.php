<?php


namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Facades\Serializer;
use App\Http\Requests\ConversationRequest;
use App\Http\Requests\ScenarioRequest;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\ScenarioResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\Scenario;

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
        $newScenario = Serializer::deserialize($request->getContent(), Scenario::class, 'json');
        /** @var Scenario $createdScenario */
        $createdScenario = ConversationDataClient::addScenario($newScenario);

        $welcomeConversation = $this->createWelcomeConversation($createdScenario);
        $createdScenario->addConversation($welcomeConversation);

        return (new ScenarioResource($createdScenario))->response()->setStatusCode(201);
    }

    /**
     * @param Scenario $scenario
     * @return Conversation
     */
    private function createWelcomeConversation(Scenario $scenario): Conversation
    {
        $conversation = new Conversation($scenario);
        $conversation->setName('Welcome Conversation');
        $conversation->setOdId('welcome_conversation');
        $conversation->setDescription('Automatically generated');
        return ConversationDataClient::addConversation($conversation);
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
}
