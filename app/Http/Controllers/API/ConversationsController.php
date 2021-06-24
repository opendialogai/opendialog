<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Facades\Serializer;
use App\Http\Requests\ConversationObjectDuplicationRequest;
use App\Http\Requests\ConversationRequest;
use App\Http\Requests\SceneRequest;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\SceneResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\Scene;

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
     * @param Conversation $conversation
     * @return Response $response
     */
    public function destroy(Conversation $conversation): Response
    {
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
        $conversation->removeUid();

        /** @var Conversation $conversation */
        $conversation = $request->setUniqueOdId($conversation, $conversation->getScenario());

        $duplicate = ConversationDataClient::addFullConversationGraph($conversation);
        $duplicate = ConversationDataClient::getFullConversationGraph($duplicate->getUid());
        return new ConversationResource($duplicate);
    }
}
