<?php


namespace App\Http\Resources;

use App\Http\Facades\Serializer;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenDialogAi\Core\Conversation\Action;
use OpenDialogAi\Core\Conversation\Behavior;
use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Transition;
use OpenDialogAi\Core\Conversation\VirtualIntent;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class IntentResource extends JsonResource
{
    public static $wrap = null;

    public static array $fields = [
        AbstractNormalizer::ATTRIBUTES => [
            Intent::UID,
            Intent::OD_ID,
            Intent::NAME,
            Intent::DESCRIPTION,
            Intent::INTERPRETER,
            Intent::CREATED_AT,
            Intent::UPDATED_AT,
            Intent::SPEAKER,
            Intent::CONFIDENCE,
            Intent::SAMPLE_UTTERANCE,
            Intent::TRANSITION => Transition::FIELDS,
            Intent::LISTENS_FOR,
            Intent::EXPECTED_ATTRIBUTES,
            Intent::VIRTUAL_INTENT => VirtualIntent::FIELDS,
            Intent::ACTIONS,
            Intent::BEHAVIORS => Behavior::FIELDS,
            Intent::CONDITIONS => Condition::FIELDS,
            Intent::TRANSITION,
            Intent::ACTIONS => Action::FIELDS
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
