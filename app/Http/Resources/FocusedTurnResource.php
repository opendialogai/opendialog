<?php


namespace App\Http\Resources;

use App\Http\Facades\Serializer;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenDialogAi\Core\Conversation\Behavior;
use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\Turn;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class FocusedTurnResource extends JsonResource
{
    public static $wrap = null;

    public static array $fields = [
        AbstractNormalizer::ATTRIBUTES => [
            Turn::UID,
            Turn::OD_ID,
            Turn::NAME,
            Turn::DESCRIPTION,
            Turn::INTERPRETER,
            Turn::CREATED_AT,
            Turn::UPDATED_AT,
            Turn::BEHAVIORS =>[
                Behavior::COMPLETING_BEHAVIOR
            ],
            Turn::CONDITIONS => [
                Condition::OPERATION,
                Condition::OPERATION_ATTRIBUTES,
                Condition::PARAMETERS
            ],
            Turn::VALID_ORIGINS,
            Turn::REQUEST_INTENTS => [
                Intent::UID,
                Intent::OD_ID,
                Intent::NAME,
                Intent::DESCRIPTION,
                Intent::SAMPLE_UTTERANCE,
                Intent::SPEAKER
            ],
            Turn::RESPONSE_INTENTS => [
                Intent::UID,
                Intent::OD_ID,
                Intent::NAME,
                Intent::DESCRIPTION,
                Intent::SAMPLE_UTTERANCE,
                Intent::SPEAKER
            ],
            Turn::SCENE => [
                Scene::UID,
                Scene::OD_ID,
                Scene::NAME,
                Scene::DESCRIPTION,
                Scene::INTERPRETER,
                Scene::CONVERSATION => [
                    Conversation::UID,
                    Conversation::OD_ID,
                    Conversation::NAME,
                    Conversation::DESCRIPTION,
                    Conversation::SCENARIO => [
                        Scenario::UID,
                        Scenario::OD_ID,
                        Scenario::NAME,
                        Scenario::DESCRIPTION
                    ]
                ]
            ]
        ]
    ];

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // reshape the response renaming conversation to focussedConversation
        $normalizedTurn = Serializer::normalize($this->resource, 'json', self::$fields);

        $normalizedTurnToReturn = [];
        $normalizedTurnToReturn['scenario'] = $normalizedTurn['scene']['conversation']['scenario'];
        $normalizedTurnToReturn['scenario']['conversation'] = $normalizedTurn['scene']['conversation'];
        unset($normalizedTurnToReturn['scenario']['conversation']['scenario']);
        $normalizedTurnToReturn['scenario']['conversation']['scene'] = $normalizedTurn['scene'];
        unset($normalizedTurnToReturn['scenario']['conversation']['scene']['conversation']);

        $normalizedTurnToReturn['scenario']['conversation']['scene']['focusedTurn']['id'] =
            $normalizedTurn['id'];
        $normalizedTurnToReturn['scenario']['conversation']['scene']['focusedTurn']['od_id'] =
            $normalizedTurn['od_id'];
        $normalizedTurnToReturn['scenario']['conversation']['scene']['focusedTurn']['name'] =
            $normalizedTurn['name'];
        $normalizedTurnToReturn['scenario']['conversation']['scene']['focusedTurn']['description'] =
            $normalizedTurn['description'];
        $normalizedTurnToReturn['scenario']['conversation']['scene']['focusedTurn']['updated_at'] =
            $normalizedTurn['updated_at'];
        $normalizedTurnToReturn['scenario']['conversation']['scene']['focusedTurn']['created_at'] =
            $normalizedTurn['created_at'];
        $normalizedTurnToReturn['scenario']['conversation']['scene']['focusedTurn']['interpreter'] =
            $normalizedTurn['interpreter'];
        $normalizedTurnToReturn['scenario']['conversation']['scene']['focusedTurn']['behaviors'] =
            $normalizedTurn['behaviors'];
        $normalizedTurnToReturn['scenario']['conversation']['scene']['focusedTurn']['conditions'] =
            $normalizedTurn['conditions'];

        // TODO dependants

        $normalizedTurnToReturn['scenario']['conversation']['scene']['focusedTurn']['intents'] =
            $normalizedTurn['intents'];

        return $normalizedTurnToReturn;
    }
}
