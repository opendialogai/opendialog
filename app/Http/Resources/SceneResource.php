<?php


namespace App\Http\Resources;

use App\Http\Facades\Serializer;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenDialogAi\Core\Conversation\Behavior;
use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\Turn;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class SceneResource extends JsonResource
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
            Scene::BEHAVIORS => Behavior::FIELDS,
            Scene::CONDITIONS => Condition::FIELDS,
            Scene::TURNS => [
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
