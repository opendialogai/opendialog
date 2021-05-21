<?php


namespace App\Http\Resources;

use App\Http\Facades\Serializer;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenDialogAi\Core\Conversation\Behavior;
use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\MessageTemplate;
use OpenDialogAi\Core\Conversation\Turn;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class TurnResource extends JsonResource
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
            Turn::VALID_ORIGINS,
            Turn::BEHAVIORS => Behavior::FIELDS,
            Turn::CONDITIONS => Condition::FIELDS,
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
