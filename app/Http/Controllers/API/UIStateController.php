<?php


namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassIntentRequest;
use App\Http\Resources\ConversationTreeResource;
use App\Http\Resources\FocusedConversationResource;
use App\Http\Resources\FocusedIntentResource;
use App\Http\Resources\FocusedScenarioResource;
use App\Http\Resources\FocusedSceneResource;
use App\Http\Resources\FocusedTurnResource;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\Turn;

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
     * Display the focused conversation.
     *
     * @param Scenario $scenario
     * @return FocusedScenarioResource
     */
    public function showFocusedScenario(Scenario $scenario): FocusedScenarioResource
    {
        return new FocusedScenarioResource($scenario);
    }

    /**
     * Display the focused conversation.
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
     * Display the focused scene.
     *
     * @param Scene $scene
     * @return FocusedSceneResource
     */
    public function showFocusedScene(Scene $scene): FocusedSceneResource
    {
        $focusedScene = ConversationDataClient::getScenarioWithFocusedScene($scene->getUid());
        return new FocusedSceneResource($focusedScene);
    }

    /**
     * Display the focused turn.
     *
     * @param Turn $turn
     * @return FocusedTurnResource
     */
    public function showFocusedTurn(Turn $turn): FocusedTurnResource
    {
        $focusedTurn = ConversationDataClient::getScenarioWithFocusedTurn($turn->getUid());
        return new FocusedTurnResource($focusedTurn);
    }

    /**
     * Display the focused intent.
     *
     * @param Intent $intent
     * @return FocusedIntentResource
     */
    public function showFocusedIntent(Intent $intent): FocusedIntentResource
    {
        $focusedIntent = ConversationDataClient::getScenarioWithFocusedIntent($intent->getUid());
        return new FocusedIntentResource($focusedIntent);
    }

    /**
     * Returns a tree of all
     *
     * @param Scenario $scenario
     * @return ConversationTreeResource
     */
    public function showConversationTree(Scenario $scenario): ConversationTreeResource
    {
        $conversationTree = ConversationDataClient::getConversationTreeByScenarioUid($scenario->getUid());
        return new ConversationTreeResource($conversationTree);
    }

    /**
     * @param MassIntentRequest $request
     * @param Turn $turn
     * @param $type
     * @return FocusedTurnResource
     */
    public function massUpdateIntents(MassIntentRequest $request, Turn $turn, $type): FocusedTurnResource
    {
        $participant = $request->participant;
        if ($type === 'response') {
            $responseParticipant = $participant;
            $requestParticipant = $this->getOppositeParticipant($responseParticipant);
        } else {
            $requestParticipant = $participant;
            $responseParticipant = $this->getOppositeParticipant($requestParticipant);
        }

        $turn->getRequestIntents()->each(function (Intent $intent) use ($requestParticipant) {
            $intent->setSpeaker($requestParticipant);
            ConversationDataClient::updateIntent($intent);
        });

        $turn->getResponseIntents()->each(function (Intent $intent) use ($responseParticipant) {
            $intent->setSpeaker($responseParticipant);
            ConversationDataClient::updateIntent($intent);
        });

        return $this->showFocusedTurn($turn);
    }

    /**
     * Returns the opposite of the given participant - either APP or USER
     *
     * @param $participant
     * @return string
     */
    private function getOppositeParticipant($participant): string
    {
        return $participant === 'APP' ? 'USER' : 'APP';
    }
}
