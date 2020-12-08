<?php


namespace App\Bot\Interpreter;

use App\Bot\Dialogflow\DialogflowClient;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\Core\Utterances\UtteranceInterface;

class Agent1DialogflowInterpreter extends AbstractDialogflowInterpreter
{
    protected static $name = 'interpreter.opendialog.agentOneDialogflow';

    public function interpret(UtteranceInterface $utterance): array
    {
        $client = resolve(DialogflowClient::class);
        $client->setLanguageCode(
            config('opendialog.interpreter_engine.dialogflow_config.languageCodes.agent_1')
        );
        $defaultProjectId = config('opendialog.interpreter_engine.dialogflow_config.project_ids.agent_1');

        Log::debug(sprintf('Agent 1 using project id: %s.', $defaultProjectId));

        $client->setDefaultProjectId($defaultProjectId);
        return $this->interpretWithClient($utterance, $client);
    }
}
