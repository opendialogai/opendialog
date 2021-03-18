<?php


namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\FocusedConversationResource;
use App\Http\Resources\FocusedScenarioResource;
use App\Http\Resources\FocusedSceneResource;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\Scene;

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
     * Display the focussed conversation.
     *
     * @param Scenario $scenario
     * @return FocusedScenarioResource
     */
    public function showFocusedScenario(Scenario $scenario): FocusedScenarioResource
    {
        $focusedConversation = ConversationDataClient::getScenarioByUid($scenario->getUid(), false);
        return new FocusedScenarioResource($focusedConversation);
    }

    /**
     * Display the focussed conversation.
     *
     * @param Conversation $conversation
     * @return FocusedConversationResource
     */
    public function showFocusedConversation(Conversation $conversation): FocusedConversationResource
    {
        $focusedConversation = ConversationDataClient::getScenarioWithFocusedConversation($conversation->getUid());
        return new FocusedConversationResource($focusedConversation);
    }

    /**
     * Display the focussed scene.
     *
     * @param Scene $scene
     * @return FocusedSceneResource
     */
    public function showFocusedScene(Scene $scene): FocusedSceneResource
    {
        $focusedConversation = ConversationDataClient::getScenarioWithFocusedScene($scene->getUid());
        return new FocusedSceneResource($focusedConversation);
    }
}
