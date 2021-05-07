<?php


namespace App\ImportExportHelpers;

use Ds\Map;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\ConversationObject;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\Turn;

class PathSubstitutionHelper
{
    public const PATH_PREFIX = '$path:';
    public const PATH_COMPONENT_SEPARATOR = '/';

    /**
     * Maps uids to paths & vice-versa, eg.
     * {
     *   '0x123' => '$path:my_scenario,
     *   '0x124' => '$path:my_scenario/my_conversation,
     *   '0x125' => '$path:my_scenario/my_conversation/my_scene,
     *   ...
     *   '$path:my_scenario' => '0x123',
     *   '$path:my_scenario/my_conversation' => '0x124',
     *   '$path:my_scenario/my_conversation/my_scene' => '0x125',
     *   ...
     * }
     *
     * @param Scenario $scenario
     * @return Map
     */
    public static function createConversationObjectUidToPathMap(Scenario $scenario): Map
    {
        $map = new Map();

        $scenarioOdId = $scenario->getOdId();
        $scenarioPath = PathSubstitutionHelper::createPath($scenarioOdId);

        $map->put($scenario->getUid(), $scenarioPath);
        $map->put($scenarioPath, $scenario->getUid());

        foreach ($scenario->getConversations() as $conversation) {
            /** @var Conversation $conversation */

            $conversationOdId = $conversation->getOdId();
            $conversationPath = PathSubstitutionHelper::createPath($scenarioOdId, $conversationOdId);

            $map->put($conversation->getUid(), $conversationPath);
            $map->put($conversationPath, $conversation->getUid());

            foreach ($conversation->getScenes() as $scene) {
                /** @var Scene $scene */

                $sceneOdId = $scene->getOdId();
                $scenePath = PathSubstitutionHelper::createPath($scenarioOdId, $conversationOdId, $sceneOdId);

                $map->put($scene->getUid(), $scenePath);
                $map->put($scenePath, $scene->getUid());

                foreach ($scene->getTurns() as $turn) {
                    /** @var Turn $turn */

                    $turnOdId = $turn->getOdId();
                    $turnPath = PathSubstitutionHelper::createPath($scenarioOdId, $conversationOdId, $sceneOdId, $turnOdId);

                    $map->put($turn->getUid(), $turnPath);
                    $map->put($turnPath, $turn->getUid());
                }
            }
        }

        return $map;
    }

    public static function stringContainsPaths(string $str): bool
    {
        return strpos($str, self::PATH_PREFIX) !== false;
    }

    /**
     * @param string ...$odIds
     * @return string
     */
    public static function createPath(string ...$odIds): string
    {
        $path = self::PATH_PREFIX . $odIds[0];

        foreach (array_slice($odIds, 1) as $odId) {
            $path .= self::PATH_COMPONENT_SEPARATOR . $odId;
        }

        return $path;
    }

    /**
     * Whether or not the given object has path-value-substitutable objects that should be patched
     *
     * @param ConversationObject $object
     * @return bool
     */
    public static function shouldPatch(ConversationObject $object): bool
    {
        if ($object->getConditions() && $object->getConditions()->isNotEmpty()) {
            return true;
        }

        if ($object instanceof Intent && $object->getTransition() && !$object->getTransition()->isEmpty()) {
            return true;
        }

        return false;
    }

    /**
     * Dehydrates the given object so that only the given UID and path-value-substitutable objects remain
     *
     * @param string $uid
     * @param ConversationObject $object
     * @return ConversationObject
     */
    public static function createPatch(string $uid, ConversationObject $object): ConversationObject
    {
        $patchObject = clone $object;
        $patchObject->dehydrate();

        $patchObject->setUid($uid);
        $patchObject->setConditions($object->getConditions());

        if ($object instanceof Intent) {
            $patchObject->setTransition($object->getTransition());
        }

        return $patchObject;
    }
}
