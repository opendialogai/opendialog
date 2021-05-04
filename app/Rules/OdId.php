<?php


namespace App\Rules;


use Illuminate\Contracts\Validation\Rule;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\ConversationObject;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\ODObjectCollection;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\Scene;

class OdId implements Rule
{
    private ?ConversationObject $parent;

    public function __construct(ConversationObject $parent = null)
    {
        $this->parent = $parent;
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
        return $this->isOdIdUniqueWithinParentScope($value, $this->parent);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'OD ID is not a unique ID.';
    }

    /**
     * Returns whether the given od_id is unique within the scope of the given parent. Eg. if the od_id is for a scene,
     * that it is unique within the given (parent) conversation
     *
     * @param string $odId
     * @param ConversationObject|null $parent If null the od_id represents a scenario
     * @return bool
     */
    private function isOdIdUniqueWithinParentScope(string $odId, ConversationObject $parent = null): bool
    {
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
            }
        }

        return $children->getObjectsWithId($odId)->isEmpty();
    }
}
