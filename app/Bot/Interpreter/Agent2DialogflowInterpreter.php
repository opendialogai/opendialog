<?php


namespace App\Bot\Interpreter;

use App\Bot\Dialogflow\DialogflowClient;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\Core\Utterances\UtteranceInterface;

class Agent2DialogflowInterpreter extends AbstractDialogflowInterpreter
{
    protected static $name = 'interpreter.opendialog.agentTwoDialogflow';

    public function interpret(UtteranceInterface $utterance): array
    {
        $client = resolve(DialogflowClient::class);
        $client->setLanguageCode(
            config('opendialog.interpreter_engine.dialogflow_config.languageCodes.agent_1')
        );
        $defaultProjectId = config('opendialog.interpreter_engine.dialogflow_config.project_ids.agent_2');

        Log::debug(sprintf('Agent 2 using project id: %s.', $defaultProjectId));

        $client->setDefaultProjectId($defaultProjectId);
        return $this->interpretWithClient($utterance, $client);
    }
}
