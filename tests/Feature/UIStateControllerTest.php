<?php


namespace Tests\Feature;

use App\Http\Facades\Serializer;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\ScenarioResource;
use App\User;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Exceptions\ConversationObjectNotFoundException;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
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
        $this->markTestSkipped(
            'Test in progress'
        );
        $fakeConversation = new Conversation();
        $fakeConversation->setUid('0x001');
        // What's the rest of the conversation I get back

//        Serializer::shouldReceive('normalize')
//            ->once()
//            ->with($fakeConversation, 'json', ConversationResource::$fields)
//            ->andReturn(json_decode('{
//            "id": "0x001",
//            "od_id": "new_example_conversation",
//            "name": "New Example conversaation",
//            "description": "An new example conversation",
//            "interpreter": "interpreter.core.nlp",
//            "behaviors": [],
//            "conditions": [],
//            "created_at": "2021-03-12T11:57:23+0000",
//            "updated_at": "2021-03-12T11:57:23+0000",
//            "scenario": {
//                "id": "0x8",
//                "od_id": "simple_scenario another update",
//                "name": "Simple Scenario updated again",
//                "description": "A Simple Scenario Again"
//            },
//            "scenes": []
//        }', true));
        ConversationDataClient::shouldReceive('getConversationByUid')
            ->once()
            ->with($fakeConversation->getUid(), false)
            ->andReturn($fakeConversation);

        ConversationDataClient::shouldReceive('getScenarioWithFocusedConversation')
            ->once()
            ->with($fakeConversation->getUid(), false)
            ->andReturn($fakeConversation);

        $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/conversation-builder/ui-state/focused/conversation/' . $fakeConversation->getUid())
            ->assertJson([

            ]);
    }
}
