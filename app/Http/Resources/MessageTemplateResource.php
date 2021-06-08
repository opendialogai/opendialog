<?php


namespace App\Http\Resources;

use App\Http\Facades\Serializer;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenDialogAi\Core\Conversation\Behavior;
use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\MessageTemplate;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\Turn;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class MessageTemplateResource extends JsonResource
{
    public static $wrap = null;

    public static array $fields = [
        AbstractNormalizer::ATTRIBUTES => [
            MessageTemplate::UID,
            MessageTemplate::OD_ID,
            MessageTemplate::NAME,
            MessageTemplate::DESCRIPTION,
            MessageTemplate::MESSAGE_MARKUP,
            MessageTemplate::CREATED_AT,
            MessageTemplate::UPDATED_AT,
            MessageTemplate::BEHAVIORS => Behavior::FIELDS,
            MessageTemplate::CONDITIONS => Condition::FIELDS,
            MessageTemplate::INTENT => [
                Intent::UID,
                Intent::OD_ID,
                Intent::NAME,
                Intent::TURN => [
                    Turn::UID,
                    Turn::OD_ID,
                    Turn::NAME,
                    Turn::SCENE => [
                        Scene::UID,
                        Scene::OD_ID,
                        Scene::NAME,
                        Scene::CONVERSATION => [
                            Conversation::UID,
                            Conversation::OD_ID,
                            Conversation::NAME,
                            Conversation::SCENARIO => [
                                Scenario::UID,
                                Scenario::OD_ID,
                                Scenario::NAME,
                            ]
                        ]
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
        return Serializer::normalize($this->resource, 'json', self::$fields);
    }
}
