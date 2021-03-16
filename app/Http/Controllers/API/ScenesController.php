<?php


namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Facades\Serializer;
use App\Http\Requests\SceneRequest;
use App\Http\Resources\SceneResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\Scene;

class ScenesController extends Controller
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
     * Display the specified scene.
     *
     * @param Scene $scene
     * @return SceneResource
     */
    public function show(Scene $scene): SceneResource
    {
        return new SceneResource($scene);
    }

    /**
     * Update the specified scenario.
     *
     * @param SceneRequest $request
     * @param Scene $scene
     * @return SceneResource
     */
    public function update(SceneRequest $request, Scene $scene): SceneResource
    {
        $conversationUpdate = Serializer::deserialize($request->getContent(), Scene::class, 'json');
        $updatedConversation = ConversationDataClient::updateScene($conversationUpdate);
        return new SceneResource($updatedConversation);
    }

    /**
     * Destroy the specified scenario.
     *
     * @param Scene $scene
     * @return Response $response
     */
    public function destroy(Scene $scene): Response
    {
        if (ConversationDataClient::deleteSceneByUid($scene->getUid())) {
            return response()->noContent(200);
        } else {
            return response('Error deleting conversation, check the logs', 500);
        }
    }
}
