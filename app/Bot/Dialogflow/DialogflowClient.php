<?php

namespace App\Bot\Dialogflow;

use Ds\Map;
use Exception;
use Google\ApiCore\ValidationException;
use Google\Cloud\Dialogflow\V2\Context;
use Google\Cloud\Dialogflow\V2\EventInput;
use Google\Cloud\Dialogflow\V2\QueryInput;
use Google\Cloud\Dialogflow\V2\QueryParameters;
use Google\Cloud\Dialogflow\V2\QueryResult;
use Google\Cloud\Dialogflow\V2\SessionsClient;
use Google\Cloud\Dialogflow\V2\TextInput;
use Google\Protobuf\Struct;
use Google\Protobuf\Value;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\ContextEngine\Facades\AttributeResolver;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\Core\Conversation\UserAttribute;
use OpenDialogAi\InterpreterEngine\Interpreters\AbstractNLUInterpreter\AbstractNLUCustomClient;
use OpenDialogAi\InterpreterEngine\Interpreters\AbstractNLUInterpreter\AbstractNLUCustomRequest;
use OpenDialogAi\InterpreterEngine\Interpreters\AbstractNLUInterpreter\AbstractNLURequestFailedException;
use OpenDialogAi\InterpreterEngine\Interpreters\AbstractNLUInterpreter\AbstractNLUResponse;

class DialogflowClient extends AbstractNLUCustomClient
{
    /**
     * @var string
     */
    private $languageCode;

    /**
     * @var string
     */
    private $defaultProjectId;

    /** @var array */
    private $allowedAttributes = [
        'authenticated',
        'language',
        'country',
        'userid',
        'first_name',
        'last_name',
        'email_address',
        'business_phone',
        'community',
        'company_name',
        'companyid',
        'product',
    ];

    public const WELCOME_EVENT = 'Welcome';

    /**
     * @inheritDoc
     */
    public function __construct($config)
    {
        $this->languageCode = $config['language_code'] ?? 'en-GB';
    }

    /**
     * @param string $languageCode
     */
    public function setLanguageCode(string $languageCode): void
    {
        $this->languageCode = $languageCode;
    }

    /**
     * @return string
     */
    public function getDefaultProjectId(): string
    {
        return $this->defaultProjectId;
    }

    /**
     * @param string $defaultProjectId
     */
    public function setDefaultProjectId(string $defaultProjectId): void
    {
        $this->defaultProjectId = $defaultProjectId;
    }

    /**
     * @inheritDoc
     * @throws AbstractNLURequestFailedException
     * @throws ValidationException
     */
    public function sendRequest($message, $projectId = null): AbstractNLUCustomRequest
    {
        $projectId = $projectId ?? $this->getDefaultProjectId();
        $client = $this->getClientForProject($projectId);

        $queryResult = null;
        try {
            $project = $projectId;
            $sessionId = ContextService::getUserContext()->getUserId() ?: uniqid();
            $session = $client->sessionName($project, $sessionId);
            $queryInput = $this->getQueryInput($message);

            $response = $client->detectIntent($session, $queryInput, [
                'queryParams' => $this->prepareQueryParams($projectId, $sessionId)
            ]);

            $queryResult = $response->getQueryResult();

            $this->storeContextAttributes(
                $this->getContextName($projectId, $sessionId),
                $queryResult
            );
        } catch (Exception $e) {
            Log::warning(sprintf('Exception caught during Dialogflow request: %s', $e->getMessage()));
        } finally {
            $client->close();
        }

        return new DialogflowRequest($queryResult);
    }

    /**
     * @inheritDoc
     */
    public function createResponse($response): AbstractNLUResponse
    {
        return new DialogflowResponse($response);
    }

    /**
     * @param $text
     * @param $languageCode
     * @return TextInput
     */
    private function getText($text, $languageCode): TextInput
    {
        $textInput = new TextInput();
        $textInput->setText($text);
        $textInput->setLanguageCode($languageCode);

        return $textInput;
    }

    /**
     * @param $eventName
     * @param $languageCode
     * @return EventInput
     */
    private function getEvent($eventName, $languageCode): EventInput
    {
        $event = new EventInput();
        $event->setName($eventName);
        $event->setLanguageCode($languageCode);

        return $event;
    }

    /**
     * @param $message
     * @return QueryInput
     */
    private function getQueryInput($message): QueryInput
    {
        $queryInput = new QueryInput();

        if ($message == '') {
            $event = $this->getEvent(self::WELCOME_EVENT, $this->languageCode);
            $queryInput->setEvent($event);
        } else {
            $textInput = $this->getText($message, $this->languageCode);
            $queryInput->setText($textInput);
        }

        return $queryInput;
    }

    /**
     * Gets the credentials path to use for the project
     *
     * @param $projectId
     * @return SessionsClient
     * @throws ValidationException
     */
    public function getClientForProject($projectId): SessionsClient
    {
        $dialogflowCredentials = config('opendialog.interpreter_engine.dialogflow_config.credentials');

        if (is_null($projectId) || empty($projectId)) {
            if (empty($dialogflowCredentials)) {
                throw new AbstractNLURequestFailedException(
                    'No Dialogflow credentials are specified in the Interpreter Engine configuration file.'
                );
            } else {
                $projectIds = array_keys($dialogflowCredentials);
                $projectId = $projectIds[0];

                Log::info(sprintf('No Dialogflow project ID was specified, defaulting to %s.', $projectId));
            }
        } else {
            if (!isset($dialogflowCredentials[$projectId])) {
                if (isset($dialogflowCredentials['_fallback'])) {
                    $projectId = '_fallback';
                } else {
                    throw new AbstractNLURequestFailedException(
                        sprintf('No credentials path found for Dialogflow agent (%s)', $projectId)
                    );
                }
            }
        }

        $credentialsPath = $dialogflowCredentials[$projectId];
        return new SessionsClient([
            'credentials' => $credentialsPath
        ]);
    }

    /**
     * @param $projectId
     * @param string $sessionId
     * @return QueryParameters
     */
    private function prepareQueryParams($projectId, string $sessionId): QueryParameters
    {
        $attributes = $this->filterAttributes(ContextService::getUserContext()->getAttributes());

        $fields = [];
        /** @var UserAttribute $attribute */
        foreach ($attributes as $attribute) {
            $fields[$attribute->getInternalAttribute()->getId()] = new Value(
                ['string_value' => $attribute->getInternalAttribute()->getValue()]
            );
        }

        return new QueryParameters([
            'payload' => new Struct([
                'fields' => [
                    'source' => new Value([
                        'string_value' => 'ACTIONS_ON_GOOGLE'
                    ]),
                ]
            ]),
            'contexts' => [
                new Context([
                    'name' => $this->getContextName($projectId, $sessionId),
                    'lifespan_count' => 1,
                    'parameters' => new Struct([
                        'fields' => $fields
                    ])
                ])
            ]
        ]);
    }

    /**
     * @param Map $attributes
     * @return Map
     */
    private function filterAttributes(Map $attributes): Map
    {
        return $attributes->filter(function ($key, UserAttribute $attribute) {
            return in_array($attribute->getId(), $this->allowedAttributes);
        });
    }

    /**
     * @param $projectId
     * @param string $sessionId
     * @return string
     */
    private function getContextName($projectId, string $sessionId): string
    {
        return "projects/$projectId/agent/sessions/$sessionId/contexts/system";
    }

    /**
     * @param $contextName
     * @param QueryResult $queryResult
     */
    private function storeContextAttributes($contextName, QueryResult $queryResult): void
    {
        /** @var Context $systemContext */
        $systemContext = null;

        /** @var Context $context */
        foreach ($queryResult->getOutputContexts() as $context) {
            if ($context->getName() == $contextName) {
                $systemContext = $context;
                break;
            }
        }

        if (is_null($systemContext) || is_null($systemContext->getParameters())) {
            return;
        }

        $userContext = ContextService::getUserContext();
        $updated = false;

        /** @var Value $value */
        foreach ($systemContext->getParameters()->getFields() as $name => $value) {
            if (in_array($name, $this->allowedAttributes)) {
                $updated = true;
                $userContext->addAttribute(
                    AttributeResolver::getAttributeFor(
                        $name,
                        $value->getStringValue()
                    )
                );
                Log::debug(sprintf('DialogflowClient: Setting user.%s to %s', $name, $value->getStringValue()));
            }
        }

        if ($updated) {
            $userContext->persist();
        }
    }
}
