<?php


namespace App\Http\Resources;

use App\Http\Facades\Serializer;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenDialogAi\Core\Conversation\Behavior;
use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\Turn;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class FocusedSceneResource extends JsonResource
{
    public static $wrap = null;

    public static array $fields = [
        AbstractNormalizer::ATTRIBUTES => [
            Scene::UID,
            Scene::OD_ID,
            Scene::NAME,
            Scene::DESCRIPTION,
            Scene::INTERPRETER,
            Scene::CREATED_AT,
            Scene::UPDATED_AT,
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
            ],
            Scene::BEHAVIORS =>[
                Behavior::COMPLETING
            ],
            Scene::CONDITIONS => [
                Condition::OPERATION,
                Condition::OPERATION_ATTRIBUTES,
                Condition::PARAMETERS
            ],
            Scene::TURNS => [
                Turn::UID,
                Turn::OD_ID,
                Turn::NAME,
                Turn::DESCRIPTION,
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
        $normalizedScene = Serializer::normalize($this->resource, 'json', self::$fields);
        $normalizedFocusedScene = [];
        $normalizedFocusedScene['scenario'] =
            $normalizedScene['conversation']['scenario'];
        $normalizedFocusedScene['scenario']['conversation']['id'] =
            $normalizedScene['conversation']['id'];
        $normalizedFocusedScene['scenario']['conversation']['od_id'] =
            $normalizedScene['conversation']['od_id'];
        $normalizedFocusedScene['scenario']['conversation']['name'] =
            $normalizedScene['conversation']['name'];
        $normalizedFocusedScene['scenario']['conversation']['description'] =
            $normalizedScene['conversation']['description'];

        $normalizedFocusedScene['scenario']['conversation']['focusedScene']['id'] =
            $normalizedScene['id'];
        $normalizedFocusedScene['scenario']['conversation']['focusedScene']['od_id'] =
            $normalizedScene['od_id'];
        $normalizedFocusedScene['scenario']['conversation']['focusedScene']['name'] =
            $normalizedScene['name'];
        $normalizedFocusedScene['scenario']['conversation']['focusedScene']['description'] =
            $normalizedScene['description'];
        $normalizedFocusedScene['scenario']['conversation']['focusedScene']['updated_at'] =
            $normalizedScene['updated_at'];
        $normalizedFocusedScene['scenario']['conversation']['focusedScene']['created_at'] =
            $normalizedScene['created_at'];
        $normalizedFocusedScene['scenario']['conversation']['focusedScene']['interpreter'] =
            $normalizedScene['interpreter'];
        $normalizedFocusedScene['scenario']['conversation']['focusedScene']['behaviors'] =
            $normalizedScene['behaviors'];
        $normalizedFocusedScene['scenario']['conversation']['focusedScene']['conditions'] =
            $normalizedScene['conditions'];
        $normalizedFocusedScene['scenario']['conversation']['focusedScene']['turns'] =
            $normalizedScene['turns'];

        return $normalizedFocusedScene;
    }
}
