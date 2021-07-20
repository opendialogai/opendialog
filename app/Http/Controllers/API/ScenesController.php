<?php


namespace App\Http\Controllers\API;

use App\Console\Facades\ImportExportSerializer;
use App\Http\Controllers\Controller;
use App\Http\Facades\Serializer;
use App\Http\Requests\ConversationObjectDuplicationRequest;
use App\Http\Requests\DeleteSceneRequest;
use App\Http\Requests\SceneRequest;
use App\Http\Requests\TurnRequest;
use App\Http\Resources\SceneResource;
use App\Http\Resources\TurnResource;
use App\ImportExportHelpers\PathSubstitutionHelper;
use App\ImportExportHelpers\ScenarioImportExportHelper;
use App\Rules\SceneInTransition;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\Normalizers\ImportExport\ScenarioNormalizer;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\Facades\SceneDataClient;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\Transition;
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
     * @param DeleteSceneRequest $request
     * @param Scene $scene
     * @return Response $response
     */
    public function destroy(DeleteSceneRequest $request, Scene $scene): Response
    {
        if ($request->json('force')) {
            $linkedIntents = SceneInTransition::getIntentsThatTransitionTo($scene->getUid());

            $linkedIntents->each(function (Intent $intent) {
                $intent->setTransition(new Transition(null, null, null));
                ConversationDataClient::updateIntent($intent);
            });
        }

        if (ConversationDataClient::deleteSceneByUid($scene->getUid())) {
            return response()->noContent(200);
        } else {
            return response('Error deleting conversation, check the logs', 500);
        }
    }

    /**
     * @param ConversationObjectDuplicationRequest $request
     * @param Scene $scene
     * @return SceneResource
     */
    public function duplicate(ConversationObjectDuplicationRequest $request, Scene $scene): SceneResource
    {
        $scene = SceneDataClient::getFullSceneGraph($scene->getUid());

        $conversation = ConversationDataClient::getConversationByUid($scene->getConversation()->getUid());

        /** @var Scene $scene */
        $scene = $request->setUniqueOdId($scene, $conversation);

        $map = PathSubstitutionHelper::createSceneMap($scene, '_duplicate', '_duplicate');

        $scene->removeUid();

        // Serialize, then deserialize the scene to convert the UID references to paths
        $serialized = ImportExportSerializer::serialize($scene, 'json', [
            ScenarioNormalizer::UID_MAP => $map
        ]);

        /** @var Scene $scene */
        $scene = ImportExportSerializer::deserialize($serialized, Scene::class, 'json');

        $scene->setConversation($conversation);

        $scene->setCreatedAt(Carbon::now());
        $scene->setUpdatedAt(Carbon::now());

        $duplicate = SceneDataClient::addFullSceneGraph($scene);
        $duplicate = SceneDataClient::getFullSceneGraph($duplicate->getUid());

        // Create a new map of all new UIDs to/from paths
        $map = PathSubstitutionHelper::createSceneMap($duplicate, '_duplicate', '_duplicate');

        // Serialize the duplicate, then deserializing using the map to replace the paths with new UIDs
        $serialized = ImportExportSerializer::serialize($duplicate, 'json');

        /** @var Scene $sceneWithPathsSubstituted */
        $sceneWithPathsSubstituted = ImportExportSerializer::deserialize($serialized, Scene::class, 'json', [
            ScenarioNormalizer::UID_MAP => $map
        ]);

        ScenarioImportExportHelper::patchScene($duplicate, $sceneWithPathsSubstituted);
        $duplicate = SceneDataClient::getFullSceneGraph($duplicate->getUid());

        return new SceneResource($duplicate);
    }
}
