<?php


namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\FocusedConversationResource;
use App\Http\Resources\FocusedScenarioResource;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\Scenario;

class UIStateController extends Controller
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
     * @return FocusedConversationResource
     */
    public function showFocusedConversation(Conversation $conversation): FocusedConversationResource
    {
        $focusedConversation = ConversationDataClient::getScenarioWithFocusedConversation($conversation->getUid());
        return new FocusedConversationResource($focusedConversation);
    }
}
