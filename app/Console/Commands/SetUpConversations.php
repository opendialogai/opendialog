<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use OpenDialogAi\ConversationBuilder\Conversation;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
use OpenDialogAi\ResponseEngine\MessageTemplate;
use OpenDialogAi\ResponseEngine\OutgoingIntent;

class SetUpConversations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'conversations:setup {--yes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sets up some example conversations for the opendialog project';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        if (!$this->option('yes')) {
            if (!$this->confirm('This will clear your local dgraph and all conversations. ' .
                'Are you sure you want to continue?')) {
                $this->info("OK, not running");
                exit;
            }
        }

        $client = app()->make(DGraphClient::class);

        $this->info('Dropping Schema');
        $client->dropSchema();

        $this->info('Init Schema');
        $client->initSchema();

        $this->info('Setting all existing conversations to unpublished');
        Conversation::all()->each(function (Conversation $conversation) {
            $conversation->status = 'validated';
            $conversation->save();
        });

        $this->publishConversation('no_match_conversation', $this->getNoMatchConversation());
        $this->publishConversation('welcome', $this->getWelcomeConversation());

        foreach ($this->getOutgoingIntents() as $intentName => $messageTemplate) {
            $intent = OutgoingIntent::updateOrCreate(['name' => $intentName]);

            // Add a default message if none exist.
            if ($intent->messageTemplates->count() === 0) {
                $messageTemplate += ['outgoing_intent_id' => $intent->id];
                MessageTemplate::create($messageTemplate);
            }
        }

        $this->warn("Conversations created. Please edit the messages for the " .
            "<options=bold>intent.core.NoMatchResponse</> and <options=bold>intent.opendialog.welcome_response intents</>");
    }

    private function publishConversation($name, $model): void
    {
        $this->info("Creating conversation $name");

        /** @var Conversation $conversation */
        $conversation = Conversation::firstOrCreate(
            ['name' => $name],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'status' => 'validated',
                'yaml_validation_status' => 'validated',
                'yaml_schema_validation_status' => 'validated',
                'scenes_validation_status' => 'validated',
                'model_validation_status' => 'validated',
                'notes' => 'auto generated',
                'model' => $model,
            ]
        );

        $this->info("Publishing conversation $name");
        $conversationModel = $conversation->buildConversation();
        $conversation->publishConversation($conversationModel);
    }

    public function getNoMatchConversation()
    {
        return <<<EOT
conversation:
  id: no_match_conversation
  scenes:
    opening_scene:
      intents:
        - u:
            i: intent.core.NoMatch
        - b:
            i: intent.core.NoMatchResponse
            completes: true
EOT;
    }

    public function getWelcomeConversation()
    {
        return <<<EOT
conversation:
  id: welcome
  scenes:
    opening_scene:
      intents:
        - u:
            i: intent.core.chatOpen
        - b:
            i: intent.opendialog.WelcomeResponse
            completes: true
EOT;
    }

    public function getOutgoingIntents()
    {
        return [
            'intent.core.NoMatchResponse' => [
                'name' => 'Did not understand',
                'conditions' => '',
                'message_markup' => '<message><text-message>This is the default No Match response. It means I didn\'t find any other conversation that could answer what you just said.</text-message></message>',
            ],
            'intent.opendialog.WelcomeResponse' => [
                'name' => 'Welcome',
                'conditions' => '',
                'message_markup' => '<message><text-message>Hi! This is my default welcome message. It pops up whenever webchat opens up. You can edit it via the bot\'s admin page.</text-message></message>',
            ],
        ];
    }
}
