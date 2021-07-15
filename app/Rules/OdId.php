<?php


namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\ConversationObject;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\ODObjectCollection;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\Turn;

class OdId implements Rule
{
    private ?ConversationObject $parent;
    private ?string $currentUid;

    public function __construct(ConversationObject $parent = null, string $currentUid = null)
    {
        $this->parent = $parent;
        $this->currentUid = $currentUid;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return self::isOdIdUniqueWithinParentScope($value, $this->parent, $this->currentUid);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute field is not unique.';
    }

    /**
     * Returns whether the given od_id is unique within the scope of the given parent. Eg. if the od_id is for a scene,
     * that it is unique within the given (parent) conversation
     *
     * @param string $odId
     * @param ConversationObject|null $parent If null the od_id represents a scenario
     * @param string|null $currentUid The current graph ID to ensure we don't check against the currently updating object
     * @return bool
     */
    public static function isOdIdUniqueWithinParentScope(
        string $odId,
        ConversationObject $parent = null,
        string $currentUid = null
    ): bool {
        $children = new ODObjectCollection();

        if (is_null($parent)) {
            $children = ConversationDataClient::getAllScenarios();
        } else {
            switch (get_class($parent)) {
                case Scenario::class:
                    /** @var Scenario $parent */
                    $children = $parent->getConversations();
                    break;

                case Conversation::class:
                    /** @var Conversation $parent */
                    $children = $parent->getScenes();
                    break;

                case Scene::class:
                    /** @var Scene $parent */
                    $children = $parent->getTurns();
                    break;

                case Turn::class:
                    /** @var Turn $parent */
                    $children = $parent->getRequestIntents()
                        ->merge($parent->getResponseIntents())
                        ->filter(function (Intent $intent) {
                            // Incoming intents shouldn't necessarily have unique OD ID's
                            return $intent->getSpeaker() === Intent::APP;
                        });
                    break;
            }
        }

        if ($currentUid) {
            // Remove the currently updating object so that it doesn't check the od_id against itself
            $children = $children->filter(fn (ConversationObject $object) => $object->getUid() != $currentUid);
        }

        return $children->getObjectsWithId($odId)->isEmpty();
    }
}
