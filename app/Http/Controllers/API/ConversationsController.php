<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Facades\Serializer;
use App\Http\Requests\ConversationRequest;
use App\Http\Resources\ConversationCollection;
use App\Http\Resources\ConversationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;

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
}
