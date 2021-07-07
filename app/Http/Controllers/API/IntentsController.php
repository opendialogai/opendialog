<?php


namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Facades\Serializer;
use App\Http\Requests\ConversationObjectDuplicationRequest;
use App\Http\Requests\IntentRequest;
use App\Http\Resources\IntentResource;
use Illuminate\Http\Response;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\Facades\IntentDataClient;
use OpenDialogAi\Core\Conversation\Intent;

class IntentsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @reIntent void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the specified Intent.
     *
     * @param Intent $intent
     * @reIntent IntentResource
     */
    public function show(Intent $intent): IntentResource
    {
        return new IntentResource($intent);
    }

    /**
     * Update the specified scenario.
     *
     * @param IntentRequest $request
     * @param Intent $intent
     * @reIntent IntentResource
     */
    public function update(IntentRequest $request, Intent $intent): IntentResource
    {
        $intentUpdate = Serializer::deserialize($request->getContent(), Intent::class, 'json');
        $updatedIntent = ConversationDataClient::updateIntent($intentUpdate);
        return new IntentResource($updatedIntent);
    }

    /**
     * Destroy the specified scenario.
     *
     * @param Intent $intent
     * @reIntent Response $response
     */
    public function destroy(Intent $intent): Response
    {
        if (ConversationDataClient::deleteIntentByUid($intent->getUid())) {
            return response()->noContent(200);
        } else {
            return response('Error deleting conversation, check the logs', 500);
        }
    }

    /**
     * @param ConversationObjectDuplicationRequest $request
     * @param Intent $intent
     * @return IntentResource
     */
    public function duplicate(ConversationObjectDuplicationRequest $request, Intent $intent): IntentResource
    {
        $isRequest = $intent->isRequestIntent();
        $intent = IntentDataClient::getFullIntentGraph($intent->getUid());

        if ($intent->getSpeaker() === Intent::APP) {
            $turn = ConversationDataClient::getTurnByUid($intent->getTurn()->getUid());

            /** @var Intent $intent */
            $intent = $request->setUniqueOdId($intent, $turn, true);
        }

        $intent->removeUid();

        $duplicate = IntentDataClient::addFullIntentGraph($intent, $isRequest);
        $duplicate = IntentDataClient::getFullIntentGraph($duplicate->getUid());

        return new IntentResource($duplicate);
    }
}
