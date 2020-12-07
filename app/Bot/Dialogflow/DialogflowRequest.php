<?php


namespace App\Bot\Dialogflow;

use Google\Cloud\Dialogflow\V2\QueryResult;
use OpenDialogAi\InterpreterEngine\Interpreters\AbstractNLUInterpreter\AbstractNLUCustomRequest;

class DialogflowRequest extends AbstractNLUCustomRequest
{
    /**
     * @var QueryResult
     */
    private $contents;

    /**
     * @var bool
     */
    private $successful;

    /**
     * DialogflowRequest constructor.
     * @param QueryResult $queryResult
     */
    public function __construct(QueryResult $queryResult)
    {
        $this->contents = $queryResult;
        $this->successful = !is_null($queryResult);
    }

    /**
     * @inheritDoc
     */
    public function getContents()
    {
        return $this->contents;
    }

    /**
     * @inheritDoc
     */
    public function isSuccessful(): bool
    {
        return $this->successful;
    }
}
