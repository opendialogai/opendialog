<?php


namespace Tests\Feature;

use App\User;
use Carbon\Carbon;
use OpenDialogAi\Core\Conversation\BehaviorsCollection;
use OpenDialogAi\Core\Conversation\ConditionCollection;
use OpenDialogAi\Core\Conversation\Exceptions\ConversationObjectNotFoundException;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\TurnCollection;
use Tests\TestCase;

class ScenesTest extends TestCase
{
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
    }

    public function testGetScenesRequiresAuthentication()
    {
        $this->get('/admin/api/conversation-builder/scenes/trigger302')
            ->assertStatus(302);
    }

    public function testGetSceneNotFound()
    {
        ConversationDataClient::shouldReceive('getSceneByUid')
            ->once()
            ->with('test', false)
            ->andThrow(new ConversationObjectNotFoundException());

        $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/conversation-builder/scenes/test')
            ->assertStatus(404);
    }


    public function testGetSceneByUid()
    {
        $fakeScene = new Scene();
        $fakeScene->setUid('0x0001');
        $fakeScene->setOdId('welcome_scene');
        $fakeScene->setName('Welcome scene');
        $fakeScene->setDescription('A welcome scene');
        $fakeScene->setInterpreter('interpreter.core.nlp');
        $fakeScene->setBehaviors(new BehaviorsCollection());
        $fakeScene->setConditions(new ConditionCollection());
        $fakeScene->setCreatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $fakeScene->setUpdatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $fakeScene->setTurns(new TurnCollection());

        ConversationDataClient::shouldReceive('getSceneByUid')
            ->once()
            ->with($fakeScene->getUid(), false)
            ->andReturn($fakeScene);

        $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/conversation-builder/scenes/' . $fakeScene->getUid())
            ->assertExactJson([
                "id" => "0x0001",
                "od_id" => "welcome_scene",
                "name" => "Welcome scene",
                "description" => "A welcome scene",
                "interpreter" => "interpreter.core.nlp",
                "created_at" => "2021-02-24T09:30:00+0000",
                "updated_at" => "2021-02-24T09:30:00+0000",
                "conditions" => [],
                "behaviors" => [],
                "turns" => []
            ]);
    }

    public function testUpdateSceneByUid()
    {
        $fakeScene = new Scene();
        $fakeScene->setUid('0x0001');
        $fakeScene->setOdId('welcome_scene');
        $fakeScene->setName('Welcome scene');
        $fakeScene->setDescription('A welcome scene');
        $fakeScene->setInterpreter('interpreter.core.nlp');
        $fakeScene->setBehaviors(new BehaviorsCollection());
        $fakeScene->setConditions(new ConditionCollection());
        $fakeScene->setCreatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $fakeScene->setUpdatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $fakeScene->setTurns(new TurnCollection());

        $fakeSceneUpdated = new Scene();
        $fakeSceneUpdated->setUid('0x0001');
        $fakeSceneUpdated->setOdId('welcome_scene');
        $fakeSceneUpdated->setName('Welcome scene updated');
        $fakeSceneUpdated->setDescription('A welcome scene updated');
        $fakeSceneUpdated->setInterpreter('interpreter.core.nlp');
        $fakeSceneUpdated->setBehaviors(new BehaviorsCollection());
        $fakeSceneUpdated->setConditions(new ConditionCollection());
        $fakeSceneUpdated->setCreatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $fakeSceneUpdated->setUpdatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $fakeSceneUpdated->setTurns(new TurnCollection());


        ConversationDataClient::shouldReceive('getSceneByUid')
            ->once()
            ->with($fakeScene->getUid(), false)
            ->andReturn($fakeScene);

        ConversationDataClient::shouldReceive('updateScene')
            ->once()
            ->withAnyArgs()
            ->andReturn($fakeSceneUpdated);

        $this->actingAs($this->user, 'api')
            ->json('PATCH', '/admin/api/conversation-builder/scenes/' . $fakeScene->getUid(), [
                'name' => $fakeSceneUpdated->getName(),
                'id' => $fakeSceneUpdated->getUid(),
                'od_id' => $fakeSceneUpdated->getODId(),
                'description' =>  $fakeSceneUpdated->getDescription()
            ])
            //->assertStatus(200)
            ->assertJson([
                "id" => "0x0001",
                "od_id" => "welcome_scene",
                "name" => "Welcome scene updated",
                "description" => "A welcome scene updated",
                "interpreter" => "interpreter.core.nlp",
                "created_at" => "2021-02-24T09:30:00+0000",
                "updated_at" => "2021-02-24T09:30:00+0000",
                "conditions" => [],
                "behaviors" => [],
                "turns" => []
            ]);
    }

    public function testDeleteSceneByUid()
    {
        $fakeScene = new Scene();
        $fakeScene->setUid('0x0001');
        $fakeScene->setOdId('welcome_scene');
        $fakeScene->setName('Welcome scene');
        $fakeScene->setDescription('A welcome scene');

        ConversationDataClient::shouldReceive('getSceneByUid')
            ->once()
            ->with($fakeScene->getUid(), false)
            ->andReturn($fakeScene);

        ConversationDataClient::shouldReceive('deleteSceneByUid')
            ->once()
            ->with($fakeScene->getUid())
            ->andReturn(true);

        $this->actingAs($this->user, 'api')
            ->json('DELETE', '/admin/api/conversation-builder/scenes/' . $fakeScene->getUid())
            ->assertStatus(200);
    }
}
