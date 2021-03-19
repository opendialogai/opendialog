<?php


namespace App\Http\Resources;

use App\Http\Facades\Serializer;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Validation\Rules\In;
use OpenDialogAi\Core\Conversation\Behavior;
use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\Turn;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class FocusedIntentResource extends JsonResource
{
    public static $wrap = null;

    public static array $fields = [
        AbstractNormalizer::ATTRIBUTES => [
            Intent::UID,
            Intent::OD_ID,
            Intent::NAME,
            Intent::DESCRIPTION,
            Intent::INTERPRETER,
            Intent::BEHAVIORS,
            Intent::CONDITIONS,
            Intent::CREATED_AT,
            Intent::UPDATED_AT,
            Intent::SPEAKER,
            Intent::SAMPLE_UTTERANCE,
            Intent::LISTENS_FOR,
            Intent::CONFIDENCE,
            Intent::EXPECTED_ATTRIBUTES,
            Intent::TRANSITION,
            Intent::VIRTUAL_INTENTS,
            Intent::ACTIONS,
            Intent::TURN => [
                Turn::UID,
                Turn::OD_ID,
                Turn::NAME,
                Turn::DESCRIPTION,
                Turn::REQUEST_INTENTS =>[
                    Intent::UID,
                    Intent::OD_ID,
                    Intent::NAME,
                    Intent::DESCRIPTION
                ],
                Turn::RESPONSE_INTENTS =>[
                    Intent::UID,
                    Intent::OD_ID,
                    Intent::NAME,
                    Intent::DESCRIPTION
                ],
                Turn::SCENE => [
                    Scene::UID,
                    Scene::OD_ID,
                    Scene::NAME,
                    Scene::DESCRIPTION,
                    Scene::CONVERSATION =>[
                        Conversation::UID,
                        Conversation::OD_ID,
                        Conversation::NAME,
                        Conversation::DESCRIPTION,
                        Conversation::SCENARIO => [
                            Scenario::UID,
                            Scenario::OD_ID,
                            Scenario::NAME,
                            Scenario::DESCRIPTION,
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
        // reshape the response renaming conversation to focussedConversation
        $normalizedIntent = Serializer::normalize($this->resource, 'json', self::$fields);

        $normalizedIntentToReturn = [];
        $normalizedIntentToReturn['scenario'] = $normalizedIntent['turn']['scene']['conversation']['scenario'];
        $normalizedIntentToReturn['scenario']['conversation'] = $normalizedIntent['turn']['scene']['conversation'];
        unset($normalizedIntentToReturn['scenario']['conversation']['scenario']);
        $normalizedIntentToReturn['scenario']['conversation']['scene'] = $normalizedIntent['turn']['scene'];
        unset($normalizedIntentToReturn['scenario']['conversation']['scene']['conversation']);
        $normalizedIntentToReturn['scenario']['conversation']['scene']['turn'] =
            $normalizedIntent['turn'];

        unset($normalizedIntentToReturn['scenario']['conversation']['scene']['turn']['scene']);

        $normalizedIntentToReturn['scenario']['conversation']['scene']['turn']['focusedIntent']['id'] =
            $normalizedIntent['id'];
        $normalizedIntentToReturn['scenario']['conversation']['scene']['turn']['focusedIntent']['od_id'] =
            $normalizedIntent['od_id'];
        $normalizedIntentToReturn['scenario']['conversation']['scene']['turn']['focusedIntent']['name'] =
            $normalizedIntent['name'];
        $normalizedIntentToReturn['scenario']['conversation']['scene']['turn']['focusedIntent']['description'] =
            $normalizedIntent['description'];
        $normalizedIntentToReturn['scenario']['conversation']['scene']['turn']['focusedIntent']['sample_utterance'] =
            $normalizedIntent['sample_utterance'];
        $normalizedIntentToReturn['scenario']['conversation']['scene']['turn']['focusedIntent']['order'] = 'REQUEST';

        return $normalizedIntentToReturn;
    }
}
