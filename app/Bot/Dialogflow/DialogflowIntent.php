<?php


namespace App\Bot\Dialogflow;

use OpenDialogAi\InterpreterEngine\Interpreters\AbstractNLUInterpreter\AbstractNLUIntent;

class DialogflowIntent extends AbstractNLUIntent
{
    /**
     * DialogflowIntent constructor.
     * @param string $displayName
     * @param float $intentDetectionConfidence
     */
    public function __construct(string $displayName, float $intentDetectionConfidence)
    {
        $this->label = $displayName;
        $this->confidence = $intentDetectionConfidence;
    }
}
