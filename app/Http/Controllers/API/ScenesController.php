<?php


namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Facades\Serializer;
use App\Http\Requests\ConversationObjectDuplicationRequest;
use App\Http\Requests\SceneRequest;
use App\Http\Requests\TurnRequest;
use App\Http\Resources\SceneResource;
use App\Http\Resources\TurnResource;
use Illuminate\Http\Response;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\Facades\SceneDataClient;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\Turn;

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
     * Returns a collection of conversations for a particular scenario.
     *
     * @param  Scene  $scene
     *
     * @return TurnResource
     */
    public function showTurnsByScene(Scene $scene): TurnResource
    {
        $turns = ConversationDataClient::getAllTurnsByScene($scene, false);
        return new TurnResource($turns);
    }

    /**
     * Store a newly created conversation against a particular scenario.
     *
     * @param  Scene        $scene
     * @param  TurnRequest  $request
     *
     * @return TurnResource
     */
    public function storeTurnAgainstScene(Scene $scene, TurnRequest $request): TurnResource
    {
        $newTurn = Serializer::deserialize($request->getContent(), Turn::class, 'json');
        $newTurn->setScene($scene);
        $turn = ConversationDataClient::addTurn($newTurn);
        return new TurnResource($turn);
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

    /**
     * @param ConversationObjectDuplicationRequest $request
     * @param Scene $conversation
     * @return SceneResource
     */
    public function duplicate(ConversationObjectDuplicationRequest $request, Scene $conversation): SceneResource
    {
        $scene = SceneDataClient::getFullSceneGraph($conversation->getUid());
        $scene->removeUid();

        /** @var Scene $scene */
        $scene = $request->setUniqueOdId($scene, $conversation->getConversation());

        $duplicate = SceneDataClient::addFullSceneGraph($scene);
        $duplicate = SceneDataClient::getFullSceneGraph($duplicate->getUid());
        return new SceneResource($duplicate);
    }
}
