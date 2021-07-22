<?php

namespace App\Console\Serializers;

use OpenDialogAi\Core\Conversation\DataClients\Serializers\BehaviorsCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\ConditionCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\ConversationCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\IntentCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\MessageTemplateCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\Normalizers\ImportExport\ActionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\Normalizers\ImportExport\ActionsCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\Normalizers\ImportExport\BehaviorNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\Normalizers\ImportExport\ConditionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\Normalizers\ImportExport\ConversationNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\Normalizers\ImportExport\IntentNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\Normalizers\ImportExport\MessageTemplateNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\Normalizers\ImportExport\ScenarioNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\Normalizers\ImportExport\SceneNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\Normalizers\ImportExport\TransitionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\Normalizers\ImportExport\TurnNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\Normalizers\ImportExport\VirtualIntentNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\ScenarioCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\SceneCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\TurnCollectionNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncode;
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
            new ScenarioNormalizer(),
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
            new VirtualIntentNormalizer(),
            new TransitionNormalizer(),
            new ActionsCollectionNormalizer(),
            new ActionNormalizer(),
            new MessageTemplateCollectionNormalizer(),
            new MessageTemplateNormalizer()
        ];
        $encoders = [new JsonEncoder(new JsonEncode([JsonEncode::OPTIONS => JSON_UNESCAPED_SLASHES]))];
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    /**
     * @param          $data
     * @param string $format
     * @param array $context
     * @return string serialized object in the form of a string
     */
    public function serialize($data, string $format, array $context = []): string
    {
        return $this->getSerializer()
            ->serialize($data, $format, $context);
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
