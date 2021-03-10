<?php


namespace App\Http\Serializers;

use Illuminate\Http\Response;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\BehaviorNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\BehaviorsCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\ScenarioNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class ConversationSerializer
{
    protected Serializer $serializer;

    public function __construct()
    {
        $normalizers = [new ScenarioNormalizer(), new BehaviorsCollectionNormalizer(), new BehaviorNormalizer()];
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
     * @return Serializer
     */
    public function getSerializer(): Serializer
    {
        return $this->serializer;
    }
}
