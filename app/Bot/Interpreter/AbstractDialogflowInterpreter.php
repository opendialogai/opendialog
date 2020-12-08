<?php

namespace App\Bot\Interpreter;

use App\Bot\Dialogflow\DialogflowClient;
use App\Bot\Dialogflow\DialogflowResponse;
use Google\Cloud\Dialogflow\V2\Intent\Message\Platform;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\ContextEngine\Facades\AttributeResolver;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Utterances\ButtonResponseUtterance;
use OpenDialogAi\Core\Utterances\UtteranceInterface;
use OpenDialogAi\InterpreterEngine\BaseInterpreter;
use OpenDialogAi\InterpreterEngine\Interpreters\AbstractNLUInterpreter\AbstractNLURequestFailedException;
use OpenDialogAi\InterpreterEngine\Interpreters\NoMatchIntent;

abstract class AbstractDialogflowInterpreter extends BaseInterpreter
{
    protected static $name = 'abstractDialogflowInterpreter';

    const NO_MATCH_INTENT    = 'intent.opendialog.dialogflowNoMatch';
    const DIALOG_FLOW_INTENT = 'intent.opendialog.dialogflow';

    abstract public function interpret(UtteranceInterface $utterance): array;

    /**
     * @param UtteranceInterface|null $utterance
     * @param DialogflowClient $client
     * @return array
     * @throws \OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported
     * @throws \OpenDialogAi\InterpreterEngine\Exceptions\InterpreterNameNotSetException
     */
    public static function interpretWithClient(?UtteranceInterface $utterance, DialogflowClient $client): array
    {
        try {
            if (is_null($utterance)) {
                $text = '';
            } elseif ($utterance->getType() == ButtonResponseUtterance::TYPE) {
                $text = $utterance->getData()['text'];
            } else {
                $text = $utterance->getText();
            }

            /** @var DialogflowResponse $result */
            $result = $client->query($text);
        } catch (AbstractNLURequestFailedException $e) {
            Log::warning(sprintf('%s failed: %s', static::getName(), $e->getMessage()));
            return [new NoMatchIntent()];
        }

        Log::debug(sprintf('%s matched: %s.', static::getName(), $result->getTopScoringIntent()->getLabel()));

        if ($result) {
            if ($result->getTopScoringIntent()->getLabel() == 'Default Fallback Intent') {
                $response = Intent::createIntentWithConfidence(self::NO_MATCH_INTENT, 1);
            } else {
                $response = Intent::createIntentWithConfidence(self::DIALOG_FLOW_INTENT, 1);
            }

            $dialogflowMessage = self::formatLinks($result->getResponse());

            $previousResponseMessage = null;
            foreach ($result->getResponseMessages() as $responseMessage) {
                if ($responseMessage->getPlatform() == Platform::ACTIONS_ON_GOOGLE) {
                    $dialogflowMessage = DialogflowMessageTransformer::interpretMessages(
                        $responseMessage,
                        $previousResponseMessage,
                        $result->getResponse()
                    );
                    $previousResponseMessage = $responseMessage;
                }
            }

            Log::warning($dialogflowMessage);

            $response->addAttribute(AttributeResolver::getAttributeFor(
                'dialogflow_message',
                $dialogflowMessage
            ));

            return [$response];
        }

        return [new NoMatchIntent()];
    }

    /**
     * @param $text
     * @return string
     */
    private static function formatLinks($text)
    {
        $regex = '/<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>/siU';
        preg_match_all($regex, $text, $links, PREG_SET_ORDER);

        $responseText = $text;

        foreach ($links as $link) {
            $linkTag = $link[0];
            $linkUrl = $link[2];
            $linkText = $link[3];

            $linkMarkup = sprintf(
                '<link new_tab="true"><url>%s</url><text>%s</text></link>',
                $linkUrl,
                $linkText
            );

            $responseText = str_replace($linkTag, $linkMarkup, $responseText);
        }

        $responseText = '<message><text-message>' . $responseText . '</text-message></message>';

        return $responseText;
    }
}
