<?php

namespace App\Console\Serializers;

use OpenDialogAi\Core\Conversation\DataClients\Serializers\BehaviorsCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\ConditionCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\ConversationCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\IntentCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\Normalizers\ImportExport\ActionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\Normalizers\ImportExport\ActionsCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\ScenarioCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\SceneCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\TurnCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\VirtualIntentCollectionNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
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
            new \OpenDialogAi\Core\Conversation\DataClients\Serializers\Normalizers\ImportExport\ConversationNormalizer(),
            new SceneCollectionNormalizer(),
            new \OpenDialogAi\Core\Conversation\DataClients\Serializers\Normalizers\ImportExport\SceneNormalizer(),
            new TurnCollectionNormalizer(),
            new \OpenDialogAi\Core\Conversation\DataClients\Serializers\Normalizers\ImportExport\TurnNormalizer(),
            new IntentCollectionNormalizer(),
            new \OpenDialogAi\Core\Conversation\DataClients\Serializers\Normalizers\ImportExport\IntentNormalizer(),
            new ConditionCollectionNormalizer(),
            new \OpenDialogAi\Core\Conversation\DataClients\Serializers\Normalizers\ImportExport\ConditionNormalizer(),
            new BehaviorsCollectionNormalizer(),
            new \OpenDialogAi\Core\Conversation\DataClients\Serializers\Normalizers\ImportExport\BehaviorNormalizer(),
            new VirtualIntentCollectionNormalizer(),
            new \OpenDialogAi\Core\Conversation\DataClients\Serializers\Normalizers\ImportExport\VirtualIntentNormalizer(),
            new \OpenDialogAi\Core\Conversation\DataClients\Serializers\Normalizers\ImportExport\TransitionNormalizer(),
            new ActionsCollectionNormalizer(),
            new ActionNormalizer()
        ];
        $encoders = [new JsonEncoder()];
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    /**
     * @param          $data
     * @param  string  $format
     *
     * @return string serialized object in the form of a string
     */
    public function serialize($data, string $format): string
    {
        return $this->getSerializer()
            ->serialize($data, $format);
    }

    /**
     * @param          $data
     * @param  string  $format
     *
     * @return array
     * @throws ExceptionInterface
     */
    public function normalize($data, string $format): array
    {
        return $this->getSerializer()
            ->normalize($data, $format);
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
}
