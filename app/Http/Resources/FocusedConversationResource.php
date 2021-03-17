<?php


namespace App\Http\Resources;

use App\Http\Facades\Serializer;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenDialogAi\Core\Conversation\Behavior;
use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\Scene;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class FocusedConversationResource extends JsonResource
{
    public static $wrap = null;

    public static array $fields = [
        AbstractNormalizer::ATTRIBUTES => [
            Conversation::UID,
            Conversation::OD_ID,
            Conversation::NAME,
            Conversation::DESCRIPTION,
            Conversation::INTERPRETER,
            Conversation::CREATED_AT,
            Conversation::UPDATED_AT,
            Conversation::SCENARIO => [
                Scenario::UID,
                Scenario::OD_ID,
                Scenario::NAME,
                Scenario::DESCRIPTION
            ],
            Conversation::BEHAVIORS =>[
                Behavior::COMPLETING
            ],
            Conversation::CONDITIONS => [
                Condition::OPERATION,
                Condition::OPERATION_ATTRIBUTES,
                Condition::PARAMETERS
            ],
//            Conversation::SCENES => [
//                Scene::UID,
//                Scene::OD_ID,
//                Scene::NAME,
//                Scene::DESCRIPTION,
//            ]
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
        $normalizedConversation = Serializer::normalize($this->resource, 'json', self::$fields);

        $normalizedFocussedConversation = [];
        $normalizedFocussedConversation['scenario'] = $normalizedConversation['scenario'];
        $normalizedFocussedConversation['scenario']['focusedConversation']['id'] =
            $normalizedConversation['id'];
        $normalizedFocussedConversation['scenario']['focusedConversation']['od_id'] =
            $normalizedConversation['od_id'];
        $normalizedFocussedConversation['scenario']['focusedConversation']['name'] =
            $normalizedConversation['name'];
        $normalizedFocussedConversation['scenario']['focusedConversation']['description'] =
            $normalizedConversation['description'];
        $normalizedFocussedConversation['scenario']['focusedConversation']['interpreter'] =
            $normalizedConversation['interpreter'];
        $normalizedFocussedConversation['scenario']['focusedConversation']['created_at'] =
            $normalizedConversation['created_at'];
        $normalizedFocussedConversation['scenario']['focusedConversation']['updated_at'] =
            $normalizedConversation['updated_at'];
        $normalizedFocussedConversation['scenario']['focusedConversation']['behaviors'] =
            $normalizedConversation['behaviors'];
        $normalizedFocussedConversation['scenario']['focusedConversation']['conditions'] =
            $normalizedConversation['conditions'];
        //$normalizedFocussedConversation['scenario']['focusedConversation']['scenes'] =
        // $normalizedConversation['scenes'];
        return $normalizedFocussedConversation;
    }
}
