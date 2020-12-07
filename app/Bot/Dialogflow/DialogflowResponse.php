<?php


namespace App\Bot\Dialogflow;

use Google\Cloud\Dialogflow\V2\QueryResult;
use Google\Protobuf\Internal\RepeatedField;
use OpenDialogAi\InterpreterEngine\Interpreters\AbstractNLUInterpreter\AbstractNLUEntity;
use OpenDialogAi\InterpreterEngine\Interpreters\AbstractNLUInterpreter\AbstractNLUResponse;

class DialogflowResponse extends AbstractNLUResponse
{
    /**
     * @var RepeatedField
     */
    private $responseMessages;

    /**
     * @var bool
     */
    private $completing;

    private $response;

    /**
     * DialogflowResponse constructor.
     * @param QueryResult $requestContents
     */
    public function __construct(QueryResult $requestContents)
    {
        $this->query = $requestContents->getQueryText();
        $this->topScoringIntent = new DialogflowIntent(
            $requestContents->getIntent()->getDisplayName(),
            $requestContents->getIntentDetectionConfidence()
        );

        $this->response = $requestContents->getFulfillmentText();

        $this->responseMessages = $requestContents->getFulfillmentMessages();

        if (!is_null($requestContents->getParameters())) {
            $entities = [];
            foreach ($requestContents->getParameters()->getFields() as $key => $value) {
                $entities[$key] = $value;
            }
            $this->createEntities($entities);
        }

        $diagnosticInfo = $requestContents->getDiagnosticInfo();
        if (!is_null($diagnosticInfo) && isset($diagnosticInfo->getFields()['end_conversation'])) {
            $this->setCompleting($diagnosticInfo->getFields()['end_conversation']->getBoolValue());
        } else {
            $this->setCompleting(false);
        }
    }

    /**
     * @param array $entities
     */
    public function createEntities(array $entities): void
    {
        foreach ($entities as $parameterName => $parameterValue) {
            $this->entities[] = $this->createEntity([
                'name' => $parameterName,
                'value' => $parameterValue->serializeToJsonString()
            ]);
        }
    }

    /**
     * @inheritDoc
     */
    public function createEntity($entity): AbstractNLUEntity
    {
        return new DialogflowEntity($entity);
    }

    /**
     * @return RepeatedField
     */
    public function getResponseMessages(): RepeatedField
    {
        return $this->responseMessages;
    }

    /**
     * @return string
     */
    public function getResponse(): string
    {
        return $this->response;
    }

    /**
     * @return bool
     */
    public function isCompleting(): bool
    {
        return $this->completing;
    }

    /**
     * @param bool $completing
     */
    public function setCompleting(bool $completing): void
    {
        $this->completing = $completing;
    }
}
