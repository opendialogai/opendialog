<?php

namespace App\Http\Controllers\API;

use App\Console\Facades\ImportExportSerializer;
use App\Http\Controllers\Controller;
use App\Http\Facades\Serializer;
use App\Http\Requests\ConversationObjectDuplicationRequest;
use App\Http\Requests\ConversationRequest;
use App\Http\Requests\DeleteConversationRequest;
use App\Http\Requests\SceneRequest;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\SceneResource;
use App\ImportExportHelpers\PathSubstitutionHelper;
use App\ImportExportHelpers\ScenarioImportExportHelper;
use App\Rules\ConversationInTransition;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\Normalizers\ImportExport\ScenarioNormalizer;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\Transition;

class ConversationsController extends Controller
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
     * Returns a collection of conversations for a particular scenario.
     *
     * @param Conversation $conversation
     * @return SceneResource
     */
    public function showScenesByConversation(Conversation $conversation): SceneResource
    {
        $scenes = ConversationDataClient::getAllScenesByConversation($conversation, false);
        return new SceneResource($scenes);
    }

    /**
     * Store a newly created conversation against a particular scenario.
     *
     * @param Conversation $conversation
     * @param SceneRequest $request
     * @return SceneResource
     */
    public function storeSceneAgainstConversation(Conversation $conversation, SceneRequest $request): SceneResource
    {
        $newScene = Serializer::deserialize($request->getContent(), Scene::class, 'json');
        $newScene->setConversation($conversation);
        $scene = ConversationDataClient::addScene($newScene);

        return new SceneResource($scene);
    }

    /**
     * Display the specified scenario.
     *
     * @param Conversation $conversation
     * @return ConversationResource
     */
    public function show(Conversation $conversation): ConversationResource
    {
        return new ConversationResource($conversation);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ConversationRequest $request
     * @return JsonResponse
     */
    public function store(ConversationRequest $request): JsonResponse
    {
        $newConversation = Serializer::deserialize($request->getContent(), Conversation::class, 'json');
        $createdConversation = ConversationDataClient::addConversation($newConversation);
        return (new ConversationResource($createdConversation))->response()->setStatusCode(201);
    }

    /**
     * Update the specified scenario.
     *
     * @param ConversationRequest $request
     * @param Conversation $conversation
     * @return ConversationResource
     */
    public function update(ConversationRequest $request, Conversation $conversation): ConversationResource
    {
        $conversationUpdate = Serializer::deserialize($request->getContent(), Conversation::class, 'json');
        $updatedConversation = ConversationDataClient::updateConversation($conversationUpdate);
        return new ConversationResource($updatedConversation);
    }

    /**
     * Destroy the specified scenario.
     *
     * @param DeleteConversationRequest $request
     * @param Conversation $conversation
     * @return Response $response
     */
    public function destroy(DeleteConversationRequest $request, Conversation $conversation): Response
    {
        if ($request->json('force')) {
            $linkedIntents = ConversationInTransition::getIntentsThatTransitionTo($conversation->getUid());

            $linkedIntents->each(function (Intent $intent) {
                $intent->setTransition(new Transition(null, null, null));
                ConversationDataClient::updateIntent($intent);
            });
        }

        if (ConversationDataClient::deleteConversationByUid($conversation->getUid())) {
            return response()->noContent(200);
        } else {
            return response('Error deleting conversation, check the logs', 500);
        }
    }

    /**
     * @param ConversationObjectDuplicationRequest $request
     * @param Conversation $conversation
     * @return ConversationResource
     */
    public function duplicate(ConversationObjectDuplicationRequest $request, Conversation $conversation): ConversationResource
    {
        $conversation = ConversationDataClient::getFullConversationGraph($conversation->getUid());

        $scenario = ConversationDataClient::getScenarioByUid($conversation->getScenario()->getUid());

        /** @var Conversation $conversation */
        $conversation = $request->setUniqueOdId($conversation, $scenario);

        $map = PathSubstitutionHelper::createConversationMap($conversation, '_duplicate');

        $conversation->removeUid();

        // Serialize, then deserialize the conversation to convert the UID references to paths
        $serialized = ImportExportSerializer::serialize($conversation, 'json', [
            ScenarioNormalizer::UID_MAP => $map
        ]);

        /** @var Conversation $conversation */
        $conversation = ImportExportSerializer::deserialize($serialized, Conversation::class, 'json');

        $conversation->setScenario($scenario);

        $conversation->setCreatedAt(Carbon::now());
        $conversation->setUpdatedAt(Carbon::now());

        $duplicate = ConversationDataClient::addFullConversationGraph($conversation);
        $duplicate = ConversationDataClient::getFullConversationGraph($duplicate->getUid());

        // Create a new map of all new UIDs to/from paths
        $map = PathSubstitutionHelper::createConversationMap($duplicate, '_duplicate');

        // Serialize the duplicate, then deserializing using the map to replace the paths with new UIDs
        $serialized = ImportExportSerializer::serialize($duplicate, 'json');

        /** @var Conversation $conversationWithPathsSubstituted */
        $conversationWithPathsSubstituted = ImportExportSerializer::deserialize($serialized, Conversation::class, 'json', [
            ScenarioNormalizer::UID_MAP => $map
        ]);

        ScenarioImportExportHelper::patchConversation($duplicate, $conversationWithPathsSubstituted);
        $duplicate = ConversationDataClient::getFullConversationGraph($duplicate->getUid());

        return new ConversationResource($duplicate);
    }
}
