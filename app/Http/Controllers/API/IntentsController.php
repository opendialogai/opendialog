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
     * @param Intent $Intent
     * @reIntent IntentResource
     */
    public function show(Intent $Intent): IntentResource
    {
        return new IntentResource($Intent);
    }

    /**
     * Update the specified scenario.
     *
     * @param IntentRequest $request
     * @param Intent $Intent
     * @reIntent IntentResource
     */
    public function update(IntentRequest $request, Intent $Intent): IntentResource
    {
        $IntentUpdate = Serializer::deserialize($request->getContent(), Intent::class, 'json');
        $updatedIntent = ConversationDataClient::updateIntent($IntentUpdate);
        return new IntentResource($updatedIntent);
    }

    /**
     * Destroy the specified scenario.
     *
     * @param Intent $Intent
     * @reIntent Response $response
     */
    public function destroy(Intent $Intent): Response
    {
        if (ConversationDataClient::deleteIntentByUid($Intent->getUid())) {
            return response()->noContent(200);
        } else {
            return response('Error deleting conversation, check the logs', 500);
        }
    }
}
