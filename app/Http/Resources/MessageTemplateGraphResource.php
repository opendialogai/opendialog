<?php


namespace App\Http\Resources;

use App\Http\Facades\Serializer;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenDialogAi\Core\Conversation\Behavior;
use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\Core\Conversation\MessageTemplate;
use OpenDialogAi\Core\Conversation\Turn;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class MessageTemplateGraphResource extends JsonResource
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
                Turn::UID
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
