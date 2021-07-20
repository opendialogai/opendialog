<?php


namespace App\Http\Controllers\API;

use App\Console\Facades\ImportExportSerializer;
use App\Http\Controllers\Controller;
use App\Http\Facades\Serializer;
use App\Http\Requests\ConversationObjectDuplicationRequest;
use App\Http\Requests\DeleteTurnRequest;
use App\Http\Requests\TurnIntentRequest;
use App\Http\Requests\TurnRequest;
use App\Http\Resources\TurnIntentResource;
use App\Http\Resources\TurnIntentResourceCollection;
use App\Http\Resources\TurnResource;
use App\ImportExportHelpers\PathSubstitutionHelper;
use App\ImportExportHelpers\ScenarioImportExportHelper;
use App\Rules\TurnInTransition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\Normalizers\ImportExport\ScenarioNormalizer;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\Facades\MessageTemplateDataClient;
use OpenDialogAi\Core\Conversation\Facades\TurnDataClient;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\MessageTemplate;
use OpenDialogAi\Core\Conversation\Transition;
use OpenDialogAi\Core\Conversation\Turn;
use OpenDialogAi\MessageBuilder\MessageMarkUpGenerator;

class TurnsController extends Controller
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
     * @param Turn $turn
     * @return JsonResponse
     */
    public function showTurnIntentsByTurn(Turn $turn): JsonResponse
    {
        $requestIntents = ConversationDataClient::getAllRequestIntentsByTurn($turn, false);
        $responseIntents = ConversationDataClient::getAllResponseIntentsByTurn($turn, false);

        $turnIntents = [];
        foreach ($requestIntents as $intent) {
            $turnIntents[] = new TurnIntentResource($intent, 'REQUEST');
        }
        foreach ($responseIntents as $intent) {
            $turnIntents[] = new TurnIntentResource($intent, 'RESPONSE');
        }
        return response()->json(new TurnIntentResourceCollection($turnIntents));
    }

    /**
     * Store a newly created TurnIntent against a particular Turn.
     *
     * @param  Turn               $turn
     * @param  TurnIntentRequest  $request
     *
     * @return TurnIntentResource
     */
    public function storeTurnIntentAgainstTurn(Turn $turn, TurnIntentRequest $request): TurnIntentResource
    {
        $newIntent = Serializer::denormalize($request->get('intent'), Intent::class, 'json');
        $newIntent->setTurn($turn);

        if ($request->get('order') === 'REQUEST') {
            $savedIntent = ConversationDataClient::addRequestIntent($newIntent);
        } else {
            $savedIntent = ConversationDataClient::addResponseIntent($newIntent);
        }

        $this->createMessageTemplate($savedIntent);

        return new TurnIntentResource($savedIntent, $request->get('order'));
    }

    /**
     * Display the specified Turn.
     *
     * @param Turn $turn
     * @return TurnResource
     */
    public function show(Turn $turn): TurnResource
    {
        return new TurnResource($turn);
    }

    /**
     * Update the specified TurnRequest.
     *
     * @param TurnRequest $request
     * @param Turn $turn
     * @return TurnResource
     */
    public function update(TurnRequest $request, Turn $turn): TurnResource
    {
        $turnUpdate = Serializer::deserialize($request->getContent(), Turn::class, 'json');
        $updatedTurn = ConversationDataClient::updateTurn($turnUpdate);
        return new TurnResource($updatedTurn);
    }

    /**
     * @param DeleteTurnRequest $request
     * @param Turn $Turn
     * @return Response $response
     */
    public function destroy(DeleteTurnRequest $request, Turn $Turn): Response
    {
        if ($request->json('force')) {
            $linkedIntents = TurnInTransition::getIntentsThatTransitionTo($Turn->getUid());

            $linkedIntents->each(function (Intent $intent) {
                $intent->setTransition(new Transition(null, null, null));
                ConversationDataClient::updateIntent($intent);
            });
        }

        if (ConversationDataClient::deleteTurnByUid($Turn->getUid())) {
            return response()->noContent(200);
        } else {
            return response('Error deleting conversation, check the logs', 500);
        }
    }

    /**
     *
     * @param Turn $turn
     * @param Intent $intent
     * @return TurnIntentResource $response
     */
    public function getTurnIntentByTurnAndIntent(Turn $turn, Intent $intent) : TurnIntentResource
    {
        $turnWithIntent = ConversationDataClient::getTurnWithIntent($turn->getUid(), $intent->getUid());
        if ($turnWithIntent->getRequestIntents()->count() > 0) {
            return new TurnIntentResource($turnWithIntent->getRequestIntents()->first(), 'REQUEST');
        }

        return new TurnIntentResource($turnWithIntent->getResponseIntents()->first(), 'RESPONSE');
    }


    /**
     * @param TurnIntentRequest $request
     * @param Turn $turn
     * @param Intent $intent
     * @return TurnIntentResource
     */
    public function updateTurnIntent(TurnIntentRequest $request, Turn $turn, Intent $intent) : TurnIntentResource
    {
        $patchIntent = Serializer::denormalize($request->get('intent'), Intent::class, 'json');
        $patchIntent->setUid($intent->getUid());
        // First update the intent data
        $updatedIntent = ConversationDataClient::updateIntent($patchIntent);
        $updatedTurnWithIntent =
            ConversationDataClient::updateTurnIntentRelation($turn->getUid(), $intent->getUid(), $request->get('order'));

        if ($updatedTurnWithIntent->getRequestIntents()->count() > 0) {
            return new TurnIntentResource($updatedTurnWithIntent->getRequestIntents()->first(), 'REQUEST');
        } elseif ($updatedTurnWithIntent->getResponseIntents()->count() > 0) {
            return new TurnIntentResource($updatedTurnWithIntent->getResponseIntents()->first(), 'RESPONSE');
        }
    }

    public function destroyTurnIntent(Turn $turn, Intent $intent) : Response
    {
        ConversationDataClient::deleteTurnIntent($turn->getUid(), $intent->getUid());

        if (ConversationDataClient::deleteIntentByUid($intent->getUid())) {
            return response()->noContent(200);
        } else {
            return response('Error deleting conversation, check the logs', 500);
        }
    }

    /**
     * Creates an intent and message template in the Response Engine if the intent being created is from the APP participant
     *
     * @param Intent $intent
     */
    private function createMessageTemplate(Intent $intent)
    {
        if ($intent->getSpeaker() === 'APP') {
            Log::info(
                sprintf('Creating a new intent and message template for intent %s as the speaker was APP', $intent->getName())
            );

            $messageTemplate = new MessageTemplate();
            $messageTemplate->setName('auto generated');
            $messageTemplate->setOdId('auto_generated');
            $messageTemplate->setIntent($intent);
            $messageTemplate->setMessageMarkup(
                (new MessageMarkUpGenerator())->addTextMessage($intent->getSampleUtterance())->getMarkUp()
            );

            MessageTemplateDataClient::addMessageTemplateToIntent($messageTemplate);
        } else {
            Log::debug(
                sprintf(
                    'Not creating a new intent and message template for intent %s as the speaker was USER',
                    $intent->getName()
                )
            );
        }
    }

    /**
     * @param ConversationObjectDuplicationRequest $request
     * @param Turn $turn
     * @return TurnResource
     */
    public function duplicate(ConversationObjectDuplicationRequest $request, Turn $turn): TurnResource
    {
        $turn = TurnDataClient::getFullTurnGraph($turn->getUid());

        $scene = ConversationDataClient::getSceneByUid($turn->getScene()->getUid());

        /** @var Turn $turn */
        $turn = $request->setUniqueOdId($turn, $scene);

        $map = PathSubstitutionHelper::createTurnMap($turn, '_duplicate', '_duplicate', '_duplicate');

        $turn->removeUid();

        // Serialize, then deserialize the turn to convert the UID references to paths
        $serialized = ImportExportSerializer::serialize($turn, 'json', [
            ScenarioNormalizer::UID_MAP => $map
        ]);

        /** @var Turn $turn */
        $turn = ImportExportSerializer::deserialize($serialized, Turn::class, 'json');

        $turn->setScene($scene);

        $turn->setCreatedAt(Carbon::now());
        $turn->setUpdatedAt(Carbon::now());

        $duplicate = TurnDataClient::addFullTurnGraph($turn);
        $duplicate = TurnDataClient::getFullTurnGraph($duplicate->getUid());

        // Create a new map of all new UIDs to/from paths
        $map = PathSubstitutionHelper::createTurnMap($duplicate, '_duplicate', '_duplicate', '_duplicate');

        // Serialize the duplicate, then deserializing using the map to replace the paths with new UIDs
        $serialized = ImportExportSerializer::serialize($duplicate, 'json');

        /** @var Turn $turnWithPathsSubstituted */
        $turnWithPathsSubstituted = ImportExportSerializer::deserialize($serialized, Turn::class, 'json', [
            ScenarioNormalizer::UID_MAP => $map
        ]);

        ScenarioImportExportHelper::patchTurn($duplicate, $turnWithPathsSubstituted);
        $duplicate = TurnDataClient::getFullTurnGraph($duplicate->getUid());

        return new TurnResource($duplicate);
    }
}
