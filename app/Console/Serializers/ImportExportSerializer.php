<?php

namespace App\Console\Serializers;

use OpenDialogAi\Core\Conversation\Action;
use OpenDialogAi\Core\Conversation\Behavior;
use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\BehaviorNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\BehaviorsCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\ConditionCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\ConditionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\ConversationCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\ConversationNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\IntentCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\IntentNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\ScenarioCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\SceneCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\SceneNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\TransitionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\TurnCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\TurnNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\VirtualIntentCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\VirtualIntentNormalizer;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\Transition;
use OpenDialogAi\Core\Conversation\Turn;
use OpenDialogAi\Core\Conversation\VirtualIntent;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Serializer;

class ImportExportSerializer
{
    protected Serializer $serializer;

    public function __construct()
    {
        $normalizers = [
            new ScenarioCollectionNormalizer(),
            new \OpenDialogAi\Core\Conversation\DataClients\Serializers\Normalizers\ImportExport\ScenarioNormalizer(),
            new ConversationCollectionNormalizer(),
            new ConversationNormalizer(),
            new SceneCollectionNormalizer(),
            new SceneNormalizer(),
            new TurnCollectionNormalizer(),
            new TurnNormalizer(),
            new IntentCollectionNormalizer(),
            new IntentNormalizer(),
            new ConditionCollectionNormalizer(),
            new ConditionNormalizer(),
            new BehaviorsCollectionNormalizer(),
            new BehaviorNormalizer(),
            new VirtualIntentCollectionNormalizer(),
            new VirtualIntentNormalizer(),
            new TransitionNormalizer()
        ];
        $encoders = [new JsonEncoder()];
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    /**
     * @param          $data
     * @param  string  $format
     * @param  array   $context
     *
     * @return string serialized object in the form of a string
     */
    public function serialize($data, string $format): string
    {
        return $this->getSerializer()
            ->serialize($data, $format, [AbstractNormalizer::ATTRIBUTES => self::getSerializationTree()]);
    }

    /**
     * @param          $data
     * @param  string  $format
     * @param  array   $context
     *
     * @return array
     * @throws ExceptionInterface
     */
    public function normalize($data, string $format): array
    {
        return $this->getSerializer()
            ->normalize($data, $format, [AbstractNormalizer::ATTRIBUTES => self::getSerializationTree()]);
    }

    /**
     * @param          $data
     * @param  string  $type
     * @param  string  $format
     * @param  array   $context
     *
     * @return object representation of $data in the form of a $type object
     */
    public function deserialize($data, string $type, string $format, array $context = [])
    {
        return $this->getSerializer()->deserialize($data, $type, $format, $context);
    }

    /**
     * @param  array   $data
     * @param  string  $type
     * @param  string  $format
     * @param  array   $context
     *
     * @return object representation of $data in the form of a $type object
     */
    public function denormalize($data, string $type, string $format, array $context = [])
    {
        return $this->getSerializer()->denormalize($data, $type, $format, $context);
    }

    /**
     * @param          $data
     * @param  string  $format
     * @param  array   $context
     *
     * @return mixed
     */
    public function decode($data, string $format, array $context = [])
    {
        return $this->getSerializer()->decode($data, 'json', $context);
    }

    /**
     * @return Serializer
     */
    public function getSerializer(): Serializer
    {
        return $this->serializer;
    }


    /**
     * @return array
     */
    public static function getSerializationTree()
    {

        /**
         * Omits UIDs, CREATED_UP, UPDATED_AT and Scenario::STATUS, Scenario::ACTIVE
         */
        return [
            Scenario::OD_ID,
            Scenario::NAME,
            Scenario::DESCRIPTION,
            Scenario::INTERPRETER,
            Scenario::CONDITIONS => Condition::FIELDS,
            Scenario::BEHAVIORS => Behavior::FIELDS,
            Scenario::CONVERSATIONS => [
                Conversation::OD_ID,
                Conversation::NAME,
                Conversation::DESCRIPTION,
                Conversation::INTERPRETER,
                Conversation::CONDITIONS => Condition::FIELDS,
                Conversation::BEHAVIORS => Behavior::FIELDS,
                Conversation::SCENES => [
                    Scene::OD_ID,
                    Scene::NAME,
                    Scene::DESCRIPTION,
                    Scene::INTERPRETER,
                    Scene::CONDITIONS => Condition::FIELDS,
                    Scene::BEHAVIORS => Behavior::FIELDS,
                    Scene::TURNS => [
                        Turn::OD_ID,
                        Turn::NAME,
                        Turn::DESCRIPTION,
                        Turn::INTERPRETER,
                        Turn::CONDITIONS => Condition::FIELDS,
                        Turn::BEHAVIORS => Behavior::FIELDS,
                        Turn::VALID_ORIGINS,
                        Turn::REQUEST_INTENTS => [
                            Intent::OD_ID,
                            Intent::NAME,
                            Intent::DESCRIPTION,
                            Intent::INTERPRETER,
                            Intent::CONDITIONS => Condition::FIELDS,
                            Intent::BEHAVIORS => Behavior::FIELDS,
                            Intent::CONFIDENCE,
                            Intent::SPEAKER,
                            Intent::SAMPLE_UTTERANCE,
                            Intent::TRANSITION => Transition::FIELDS,
                            Intent::VIRTUAL_INTENTS => VirtualIntent::FIELDS,
                            Intent::LISTENS_FOR,
                            Intent::EXPECTED_ATTRIBUTES,
                            Intent::ACTIONS => Action::FIELDS
                        ],
                        Turn::RESPONSE_INTENTS => [
                            Intent::OD_ID,
                            Intent::NAME,
                            Intent::DESCRIPTION,
                            Intent::INTERPRETER,
                            Intent::CONDITIONS => Condition::FIELDS,
                            Intent::BEHAVIORS => Behavior::FIELDS,
                            Intent::CONFIDENCE,
                            Intent::SPEAKER,
                            Intent::SAMPLE_UTTERANCE,
                            Intent::TRANSITION => Transition::FIELDS,
                            Intent::VIRTUAL_INTENTS => VirtualIntent::FIELDS,
                            Intent::LISTENS_FOR,
                            Intent::EXPECTED_ATTRIBUTES,
                            Intent::ACTIONS => Action::FIELDS
                        ]
                    ]
                ]
            ]
        ];
    }
}
