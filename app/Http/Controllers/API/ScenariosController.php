<?php


namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Facades\Serializer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
     * @return Response
     */
    public function index(): Response
    {
        $scenarios = ConversationDataClient::getAllScenarios();
        $responseBody = Serializer::serialize($scenarios, 'json');

        return response($responseBody, 200);
    }

    /**
     * Display the specified scenario.
     *
     * @param string $id
     * @return Response
     */
    public function show(string $id): Response
    {
        if ($scenario = ConversationDataClient::getScenarioByUid($id)) {
            $responseBody = Serializer::serialize($scenario, 'json');
            return response($responseBody, 200);
        }
        return response()->noContent(404);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        if ($newScenario = Serializer::deserialize($request->getContent(), Scenario::class, 'json')) {
            $createdScenario = ConversationDataClient::addScenario($newScenario);
            $responseBody = Serializer::serialize($createdScenario, 'json');
            return response($responseBody, 201);
        }
    }

    /**
     * Update the specified scenario.
     *
     * @param Request $request
     * @param string $id
     * @return Response
     */
    public function update(Request $request, string $id): Response
    {
        if ($scenario = Serializer::deserialize($request->getContent(), Scenario::class, 'json')) {
            $updatedScenario = ConversationDataClient::updateScenario($scenario);
            $responseBody = Serializer::serialize($updatedScenario, 'json');
            return response($responseBody, 200);
        }
        return response()->noContent(404);
    }

    /**
     * Destroy the specified scenario.
     *
     * @param string $id
     * @return Response $response
     */
    public function destroy(string $id): Response
    {
        if ($scenario = ConversationDataClient::getScenarioByUid($id)) {
            if (ConversationDataClient::deleteScenarioByUid($id)) {
                return response()->noContent(200);
            } else {
                return response('Error deleting scenario, check the logs', 500);
            }
        }
        return response()->noContent(404);
    }
}
