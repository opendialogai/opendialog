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

class ConversationTreeResource extends JsonResource
{
    public static $wrap = null;

    public static array $fields = [
        AbstractNormalizer::ATTRIBUTES => [
            Scenario::UID,
            Scenario::CONVERSATIONS => [
                Conversation::UID,
                Conversation::OD_ID,
                Conversation::NAME,
                Conversation::SCENES => [
                    Scene::UID,
                    Scene::OD_ID,
                    Scene::NAME,
                    Scene::TURNS => [
                        Turn::UID,
                        Turn::OD_ID,
                        Turn::NAME
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
