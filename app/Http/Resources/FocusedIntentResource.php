<?php


namespace App\Http\Resources;

use App\Http\Facades\Serializer;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenDialogAi\Core\Conversation\Action;
use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\MessageTemplate;
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
            Intent::BEHAVIORS => [],
            Intent::CONDITIONS => [
                Condition::OPERATION,
                Condition::OPERATION_ATTRIBUTES,
                Condition::PARAMETERS
            ],
            Intent::CREATED_AT,
            Intent::UPDATED_AT,
            Intent::SPEAKER,
            Intent::SAMPLE_UTTERANCE,
            Intent::LISTENS_FOR,
            Intent::CONFIDENCE,
            Intent::EXPECTED_ATTRIBUTES,
            Intent::TRANSITION,
            Intent::VIRTUAL_INTENT,
            Intent::ACTIONS => Action::FIELDS,
            Intent::MESSAGE_TEMPLATES => [
                MessageTemplate::UID,
                MessageTemplate::OD_ID,
                MessageTemplate::NAME,
                MessageTemplate::MESSAGE_MARKUP,
                MessageTemplate::DESCRIPTION,
                MessageTemplate::CREATED_AT,
                MessageTemplate::UPDATED_AT,
                MessageTemplate::CONDITIONS => [
                    Condition::OPERATION,
                    Condition::OPERATION_ATTRIBUTES,
                    Condition::PARAMETERS
                ],
            ],
            Intent::TURN => [
                Turn::UID,
                Turn::OD_ID,
                Turn::NAME,
                Turn::DESCRIPTION,
                Turn::REQUEST_INTENTS =>[
                    Intent::UID,
                    Intent::OD_ID,
                    Intent::NAME,
                    Intent::DESCRIPTION,
                    Intent::INTERPRETER,
                    Intent::SAMPLE_UTTERANCE,
                    Intent::SPEAKER,
                    Intent::TRANSITION,
                    Intent::BEHAVIORS => [],
                    Intent::CONFIDENCE,
                    Intent::EXPECTED_ATTRIBUTES,
                    Intent::CONDITIONS => [
                        Condition::OPERATION,
                        Condition::OPERATION_ATTRIBUTES,
                        Condition::PARAMETERS
                    ],
                    Intent::MESSAGE_TEMPLATES => [
                        MessageTemplate::UID,
                        MessageTemplate::OD_ID,
                        MessageTemplate::NAME,
                        MessageTemplate::MESSAGE_MARKUP,
                        MessageTemplate::DESCRIPTION,
                        MessageTemplate::CREATED_AT,
                        MessageTemplate::UPDATED_AT,
                        MessageTemplate::CONDITIONS => [
                            Condition::OPERATION,
                            Condition::OPERATION_ATTRIBUTES,
                            Condition::PARAMETERS
                        ],
                    ],
                    Intent::ACTIONS => Action::FIELDS
                ],
                Turn::RESPONSE_INTENTS =>[
                    Intent::UID,
                    Intent::OD_ID,
                    Intent::NAME,
                    Intent::DESCRIPTION,
                    Intent::SAMPLE_UTTERANCE,
                    Intent::SPEAKER,
                    Intent::TRANSITION,
                    Intent::BEHAVIORS => [],
                    Intent::CONFIDENCE,
                    Intent::EXPECTED_ATTRIBUTES,
                    Intent::CONDITIONS => [
                        Condition::OPERATION,
                        Condition::OPERATION_ATTRIBUTES,
                        Condition::PARAMETERS
                    ],
                    Intent::ACTIONS => Action::FIELDS,
                    Intent::MESSAGE_TEMPLATES => [
                        MessageTemplate::UID,
                        MessageTemplate::OD_ID,
                        MessageTemplate::NAME,
                        MessageTemplate::MESSAGE_MARKUP,
                        MessageTemplate::DESCRIPTION,
                        MessageTemplate::CREATED_AT,
                        MessageTemplate::UPDATED_AT,
                        MessageTemplate::CONDITIONS => [
                            Condition::OPERATION,
                            Condition::OPERATION_ATTRIBUTES,
                            Condition::PARAMETERS
                        ],
                    ],
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

        $normalizedIntentToReturn['scenario']['conversation']['scene']['turn']['focusedIntent'] = $normalizedIntent;
        $intentsArray = $normalizedIntentToReturn['scenario']['conversation']['scene']['turn']['intents'];

        $order = "";
        foreach ($intentsArray as $intent) {
            if ($intent['intent']['id'] === $normalizedIntent['id']) {
                $order = $intent['order'];
                break;
            }
        }

        $normalizedIntentToReturn['scenario']['conversation']['scene']['turn']['focusedIntent']['order'] = $order;
        $normalizedIntentToReturn['scenario']['conversation']['scene']['turn']['focusedIntent']['speaker'] =
            $normalizedIntent['speaker'];
        return $normalizedIntentToReturn;
    }
}
