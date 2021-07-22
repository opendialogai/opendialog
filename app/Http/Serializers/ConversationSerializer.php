<?php


namespace App\Http\Serializers;

use OpenDialogAi\Core\Conversation\DataClients\Serializers\ActionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\ActionsCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\ApiTurnNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\BehaviorNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\BehaviorsCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\ConditionCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\ConditionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\ConversationCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\ConversationNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\IntentCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\IntentNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\MessageTemplateCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\MessageTemplateNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\ScenarioCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\ScenarioNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\SceneCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\SceneNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\TransitionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\TurnCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\VirtualIntentNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Serializer;

class ConversationSerializer
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
            new ApiTurnNormalizer(),
            new IntentCollectionNormalizer(),
            new IntentNormalizer(),
            new ConditionCollectionNormalizer(),
            new ConditionNormalizer(),
            new BehaviorsCollectionNormalizer(),
            new BehaviorNormalizer(),
            new VirtualIntentNormalizer(),
            new TransitionNormalizer(),
            new ActionNormalizer(),
            new ActionsCollectionNormalizer(),
            new MessageTemplateNormalizer(),
            new MessageTemplateCollectionNormalizer()
        ];
        $encoders = [new JsonEncoder()];
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    /**
     * @param $data
     * @param string $format
     * @param array $context
     * @return string serialized object in the form of a string
     */
    public function serialize($data, string $format, array $context = []): string
    {
        return $this->getSerializer()->serialize($data, $format, $context);
    }

    /**
     * @param $data
     * @param string $format
     * @param array $context
     * @return array
     * @throws ExceptionInterface
     */
    public function normalize($data, string $format, array $context = []): array
    {
        return $this->getSerializer()->normalize($data, $format, $context);
    }

    /**
     * @param $data
     * @param string $type
     * @param string $format
     * @param array $context
     * @return object representation of $data in the form of a $type object
     */
    public function deserialize($data, string $type, string $format, array $context = [])
    {
        return $this->getSerializer()->deserialize($data, $type, $format, $context);
    }

    /**
     * @param array $data
     * @param string $type
     * @param string $format
     * @param array $context
     * @return object representation of $data in the form of a $type object
     */
    public function denormalize($data, string $type, string $format, array $context = [])
    {
        return $this->getSerializer()->denormalize($data, $type, $format, $context);
    }
    /**
     * @return Serializer
     */
    public function getSerializer(): Serializer
    {
        return $this->serializer;
    }
}
