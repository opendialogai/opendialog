<?php


namespace App\Bot\Dialogflow;

use OpenDialogAi\InterpreterEngine\Interpreters\AbstractNLUInterpreter\AbstractNLUEntity;

class DialogflowEntity extends AbstractNLUEntity
{
    /**
     * DialogflowEntity constructor.
     * @param $entity
     */
    public function __construct($entity)
    {
        $this->type = $entity['name'];
        $this->resolutionValues[0] = $entity['value'];
    }
}
