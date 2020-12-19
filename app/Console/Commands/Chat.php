<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\ContextEngine\Contexts\User\UserService;
use OpenDialogAi\Core\Controllers\OpenDialogController;
use OpenDialogAi\Core\Utterances\Webchat\WebchatUrlClickUtterance;
use OpenDialogAi\ResponseEngine\LinkClickInterface;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatMessages;
use OpenDialogAi\SensorEngine\Sensors\WebchatSensor;

class Chat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature =
        'opendialog:chat {--intent=intent.core.welcome : The starting intent} {--userid= : The user who is interacting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Engage in a conversation with OpenDialog';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @param \OpenDialogAi\ContextEngine\Contexts\User\UserService $userService
     * @param \OpenDialogAi\SensorEngine\Sensors\WebchatSensor $webChatSensor
     * @param \OpenDialogAi\Core\Controllers\OpenDialogController $odController
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \OpenDialogAi\ContextEngine\Contexts\User\CurrentIntentNotSetException
     * @throws \OpenDialogAi\ConversationEngine\ConversationStore\EIModelCreatorException
     * @throws \OpenDialogAi\Core\Graph\Node\NodeDoesNotExistException
     * @throws \OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported
     * @throws \OpenDialogAi\Core\Utterances\Exceptions\UtteranceUnknownMessageType
     */
    public function handle(UserService $userService, WebchatSensor $webChatSensor, OpenDialogController $odController)
    {
        $userId = $this->input->getOption('userid');

        $userId = !empty($userId) ? $userId : $this->ask('Hello human, please enter your identification number');
        $user = User::find($userId);

        if (empty($user)) {
            $this->output->writeln("<error>Foolish human! There is no such person.</error>");
            return;
        }

        $this->output->writeln("<info>Identified {$user->email}</info>");
        $openingIntent = $this->input->getOption('intent');

        $chatRequest = $this->createWebchatRequest($user, 'trigger', $openingIntent);

        while (!empty($chatRequest)) {
            // Get the Utterance.
            $utterance = $webChatSensor->interpret($chatRequest);

            /** @var WebChatMessages $messageWrapper */
            $messageWrapper = $odController->runConversation($utterance);

            $messages = $messageWrapper->getMessageToPost();

            $chatRequest = $this->renderMessages($user, $messages);
        }

        $this->output->writeln("<comment>The end</comment>");
    }

    protected function createWebchatRequest($user, $type, $intent = '', $text = '', $value = '')
    {
        $chatData = [
            'notification' => 'message',
            'user_id' => $user->email,
            'author' => $user->email,
            'content' => [
                'type' => $type,
                'author' => $user->email,
                'callback_id' => $intent,
                'data' => [
                    'text' => $text,
                    'value' => $value,
                    'date' => "Wed 16 Dec",
                    'time' => "11:50:40 AM",
                ],
                'mode' => 'webchat',
                'modeInstance' => 0,
                'user_id' => $user->email,
                'user' => [
                    "first_name" => $user->name,
                    "last_name" => "",
                    "email" => $user->email,
                    "external_id" => $user->id,
                ]
            ]
        ];

        return $this->createRequest('POST', json_encode($chatData));
    }

    /**
     * @param $user
     * @param $messages
     *
     * @return \Illuminate\Http\Request|\Symfony\Component\HttpFoundation\Request|void
     */
    protected function renderMessages($user, $messages)
    {
        if (empty($messages)) {
            $this->output->writeln("<info>Empty list of messages</info>");
            return;
        }

        foreach ($messages as $message) {
            $response = $this->renderMessage($user, $message);

            if (!empty($response)) {
                return $response;
            }
        }

        $freeTextResponse = $this->ask('');
        return $this->createWebchatRequest($user, 'text', '', $freeTextResponse);
    }

    protected function renderMessage($user, $message)
    {
        if (empty($message)) {
            $this->output->writeln("<info>Empty response</info>");
            return null;
        }

        $this->output->writeln("\n------------------------------------------------------------\n"
            . "<info>{$message['type']} message ({$message['intent']})</info>");

        if ($this->output->isVerbose()) {
            print_r($message);
        }

        switch ($message['type']) {
            case 'button':
                $optionsText = '';
                $options = [];

                foreach ($message['data']['buttons'] as $id => $button) {
                    $optionsText .= "   > {$button['text']} ({$button['callback_id']})\n";
                    $options[] = $button['text'];
                }

                $result = $this->askWithCompletion("<comment>{$message['data']['text']}</comment>"
                    . "\n<info> Button options:\n{$optionsText}</info>", $options, $message['data']['buttons'][0]['text']);
                $choice = array_search($result, $options);

                if ($choice === false) {
                    $this->output->writeln("<error>Foolish human! That was not an option.</error>");
                    return null;
                }

                return $this->createWebchatRequest(
                    $user,
                    'button_response',
                    $message['data']['buttons'][$choice]['callback_id'],
                    $message['data']['buttons'][$choice]['text']
                );

            case 'text':
                $this->output->writeln("<comment>{$message['data']['text']}</comment>");
                break;

            default:
                $this->output->writeln("<error>CLI cannot deal with a {$message['type']} response</error>");
                break;
        }
    }

    protected function createRequest(
        $method,
        $content,
        $uri = '/test',
        $server = ['CONTENT_TYPE' => 'application/json'],
        $parameters = [],
        $cookies = [],
        $files = []
    ) {
        $request = new \Illuminate\Http\Request;
        return $request->createFromBase(
            \Symfony\Component\HttpFoundation\Request::create($uri, $method, $parameters, $cookies, $files, $server, $content)
        );
    }
}
