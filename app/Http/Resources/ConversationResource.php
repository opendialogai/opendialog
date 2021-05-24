<?php

namespace App\Http\Resources;

use App\Http\Facades\Serializer;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenDialogAi\Core\Conversation\Behavior;
use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\Turn;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class ConversationResource extends JsonResource
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
            Conversation::CONDITIONS => [
                Condition::OPERATION,
                Condition::OPERATION_ATTRIBUTES,
                Condition::PARAMETERS
            ],
            Conversation::BEHAVIORS => [
                Behavior::COMPLETING_BEHAVIOR
            ],
            Conversation::SCENES => [
                Scene::UID,
                Scene::OD_ID,
                Scene::NAME,
                Scene::TURNS => [
                    Turn::UID,
                    Turn::OD_ID,
                    Turn::NAME,
                    Turn::DESCRIPTION,
                    Turn::REQUEST_INTENTS => [
                        Intent::UID,
                        Intent::NAME,
                        Intent::SAMPLE_UTTERANCE,
                        Intent::SPEAKER
                    ],
                    Turn::RESPONSE_INTENTS => [
                        Intent::UID,
                        Intent::NAME,
                        Intent::SAMPLE_UTTERANCE,
                        Intent::SPEAKER,
                    ],
                ]
            ]
        ]
    ];

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return Serializer::normalize($this->resource, 'json', self::$fields);
    }
}
