<?php


namespace Tests\Feature;

use App\Http\Facades\Serializer;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\ScenarioResource;
use App\User;
use Carbon\Carbon;
use OpenDialogAi\Core\Conversation\BehaviorsCollection;
use OpenDialogAi\Core\Conversation\ConditionCollection;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Exceptions\ConversationObjectNotFoundException;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\Scenario;
use Tests\TestCase;

class UIStateControllerTest extends TestCase
{
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
    }


    public function testFocusedConversationNotFound()
    {
        $this->markTestSkipped(
            'Currently the exception thrown in the ConversationDataClient isnt the correct one'
        );
        ConversationDataClient::shouldReceive('getScenarioWithFocusedConversation')
            ->once()
            ->with('test', false)
            ->andThrow(new ConversationObjectNotFoundException());

        $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/conversation-builder/ui-state/focused/conversation/test')
            ->assertStatus(404);
    }

    public function testGetFocusedConversation()
    {
        $fakeScenario = new Scenario();
        $fakeScenario->setUid('0x0001');
        $fakeScenario->setName("Example scenario");
        $fakeScenario->setOdId('example_scenario');
        $fakeScenario->setDescription('An example scenario');

        $fakeConversation = new Conversation();
        $fakeConversation->setUid('0x0002');
        $fakeConversation->setName('New Example conversation');
        $fakeConversation->setOdId('new_example_conversation');
        $fakeConversation->setDescription("An new example conversation");
        $fakeConversation->setInterpreter('interpreter.core.nlp');
        $fakeConversation->setBehaviors(new BehaviorsCollection());
        $fakeConversation->setConditions(new ConditionCollection());
        $fakeConversation->setCreatedAt(Carbon::parse('2021-03-12T11:57:23+0000'));
        $fakeConversation->setUpdatedAt(Carbon::parse('2021-03-12T11:57:23+0000'));

        $fakeConversation->setScenario($fakeScenario);

        ConversationDataClient::shouldReceive('getConversationByUid')
            ->once()
            ->with($fakeConversation->getUid(), false)
            ->andReturn($fakeConversation);

        ConversationDataClient::shouldReceive('getScenarioWithFocusedConversation')
            ->once()
            ->with($fakeConversation->getUid())
            ->andReturn($fakeConversation);

        $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/conversation-builder/ui-state/focused/conversation/' . $fakeConversation->getUid())
            ->assertExactJson([
                'scenario' => [
                    'id'=> '0x0001',
                    'od_id'=> 'example_scenario',
                    'name'=> 'Example scenario',
                    'description'=> 'An example scenario',
                   'focusedConversation' => [
                       "id" => "0x0002",
                       "name" => "New Example conversation",
                       "od_id" => "new_example_conversation",
                       "description" => "An new example conversation",
                       "interpreter" => "interpreter.core.nlp",
                       "behaviors" => [],
                       "conditions" => [],
                       "created_at" => "2021-03-12T11:57:23+0000",
                       "updated_at" => "2021-03-12T11:57:23+0000"
                   ]
                ]
            ]);
    }
}
