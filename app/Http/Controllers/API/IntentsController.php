<?php


namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Facades\Serializer;
use App\Http\Requests\IntentRequest;
use App\Http\Requests\TurnRequest;
use App\Http\Resources\IntentResource;
use App\Http\Resources\TurnResource;
use Illuminate\Http\Response;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\Turn;

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
}
