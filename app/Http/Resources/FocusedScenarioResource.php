<?php


namespace App\Http\Resources;

use App\Http\Facades\Serializer;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenDialogAi\Core\Conversation\Behavior;
use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Scenario;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class FocusedScenarioResource extends JsonResource
{
    public static $wrap = null;

    public static array $fields = [
        AbstractNormalizer::ATTRIBUTES => [
            Scenario::UID,
            Scenario::OD_ID,
            Scenario::NAME,
            Scenario::DESCRIPTION,
            Scenario::INTERPRETER,
            Scenario::CREATED_AT,
            Scenario::UPDATED_AT,
            Scenario::ACTIVE,
            Scenario::STATUS,
            Scenario::BEHAVIORS => Behavior::FIELDS,
            Scenario::CONDITIONS => Condition::FIELDS,
            Scenario::CONVERSATIONS => [
                Conversation::UID
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
        $normalizedScenario = Serializer::normalize($this->resource, 'json', self::$fields);
        return ['focusedScenario' => $normalizedScenario];
    }
}
