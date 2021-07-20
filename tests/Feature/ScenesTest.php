<?php


namespace Tests\Feature;

use App\User;
use Carbon\Carbon;
use OpenDialogAi\Core\Conversation\BehaviorsCollection;
use OpenDialogAi\Core\Conversation\ConditionCollection;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Exceptions\ConversationObjectNotFoundException;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\Facades\IntentDataClient;
use OpenDialogAi\Core\Conversation\Facades\SceneDataClient;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\IntentCollection;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\SceneCollection;
use OpenDialogAi\Core\Conversation\Transition;
use OpenDialogAi\Core\Conversation\Turn;
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


    public function testGetAllScenesByConversation()
    {
        $fakeConversation = new Conversation();
        $fakeConversation->setUid('0x0002');
        $fakeConversation->setName('New Example conversation 1');
        $fakeConversation->setOdId('new_example_conversation_1');
        $fakeConversation->setDescription("An new example conversation 1");

        $fakeScene1 = new Scene();
        $fakeScene1->setUid('0x0003');
        $fakeScene1->setOdId('welcome_scene_1');
        $fakeScene1->setName('Welcome scene 1');
        $fakeScene1->setDescription('A welcome scene 1');
        $fakeScene1->setCreatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $fakeScene1->setUpdatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $fakeScene1->setInterpreter('interpreter.core.nlp');
        $fakeScene1->setConditions(new ConditionCollection());
        $fakeScene1->setBehaviors(new BehaviorsCollection());
        $fakeScene1->setTurns(new TurnCollection());

        $fakeScene2 = new Scene();
        $fakeScene2->setUid('0x0004');
        $fakeScene2->setOdId('welcome_scene_2');
        $fakeScene2->setName('Welcome scene 2');
        $fakeScene2->setDescription('A welcome scene 2');
        $fakeScene2->setCreatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $fakeScene2->setUpdatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $fakeScene2->setInterpreter('interpreter.core.nlp');
        $fakeScene2->setConditions(new ConditionCollection());
        $fakeScene2->setBehaviors(new BehaviorsCollection());
        $fakeScene2->setTurns(new TurnCollection());

        $fakeSceneCollection = new SceneCollection();
        $fakeSceneCollection->addObject($fakeScene1);
        $fakeSceneCollection->addObject($fakeScene2);

        ConversationDataClient::shouldReceive('getConversationByUid')
            ->once()
            ->with($fakeConversation->getUid(), false)
            ->andReturn($fakeConversation);

        ConversationDataClient::shouldReceive('getAllScenesByConversation')
            ->once()
            ->andReturn($fakeSceneCollection);

        $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/conversation-builder/conversations/' . $fakeConversation->getUid() . '/scenes')
            ->assertStatus(200)
            ->assertJson([[
                "id" => "0x0003",
                "od_id" => "welcome_scene_1",
                "name" => "Welcome scene 1",
                "description" => "A welcome scene 1",
                "interpreter" => 'interpreter.core.nlp',
                "created_at" => "2021-02-24T09:30:00+0000",
                "updated_at" => "2021-02-24T09:30:00+0000",
                "conditions" => [],
                "behaviors" => [],
                "turns" => []
            ], [
                "id" => "0x0004",
                "od_id" => "welcome_scene_2",
                "name" => "Welcome scene 2",
                "description" => "A welcome scene 2",
                "interpreter" => 'interpreter.core.nlp',
                "created_at" =>"2021-02-24T09:30:00+0000",
                "updated_at" => "2021-02-24T09:30:00+0000",
                "conditions" => [],
                "behaviors" => [],
                "turns" => []
            ]]);
    }

    public function testAddSceneToConversation()
    {
        $fakeConversation = new Conversation();
        $fakeConversation->setUid('0x0002');
        $fakeConversation->setName('New Example conversation 1');
        $fakeConversation->setOdId('new_example_conversation_1');
        $fakeConversation->setDescription("An new example conversation 1");

        $fakeScene = new Scene();
        $fakeScene->setUid('0x0003');
        $fakeScene->setOdId('welcome_scene');
        $fakeScene->setName('Welcome scene');
        $fakeScene->setDescription('A welcome scene');
        $fakeScene->setCreatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $fakeScene->setUpdatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $fakeScene->setInterpreter('interpreter.core.nlp');
        $fakeScene->setConditions(new ConditionCollection());
        $fakeScene->setBehaviors(new BehaviorsCollection());
        $fakeScene->setTurns(new TurnCollection());

        ConversationDataClient::shouldReceive('addScene')
            ->once()
            ->withAnyArgs()
            ->andReturn($fakeScene);

        ConversationDataClient::shouldReceive('getConversationByUid')
            ->once()
            ->with($fakeConversation->getUid(), false)
            ->andReturn($fakeConversation);

        $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/conversation-builder/conversations/' . $fakeConversation->getUid() . '/scenes', [
                "od_id" => "welcome_scene",
                "name" => "Welcome scene",
                "description" => "A welcome scene",
                "interpreter" => "interpreter.core.nlp",
                "conditions" => [],
                "behaviors" => [],
                "turns" => []
            ])
            ->assertStatus(200)
            ->assertJson([
                "id" => "0x0003",
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
                'description' =>  $fakeSceneUpdated->getDescription()
            ])
            //->assertStatus(200)
            ->assertJson([
                "id" => "0x0001",
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

        IntentDataClient::shouldReceive('getIntentWithSceneTransition')
            ->once()
            ->with($fakeScene->getUid())
            ->andReturn(new IntentCollection());

        $this->actingAs($this->user, 'api')
            ->json('DELETE', '/admin/api/conversation-builder/scenes/' . $fakeScene->getUid())
            ->assertStatus(200);
    }

    public function testDeleteSceneByUidInUse()
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
            ->never();

        $scene = new Scene();
        $scene->setUid('different');

        IntentDataClient::shouldReceive('getIntentWithSceneTransition')
            ->once()
            ->with($fakeScene->getUid())
            ->andReturn(new IntentCollection([new Intent(new Turn($scene))]));

        $this->actingAs($this->user, 'api')
            ->json('DELETE', '/admin/api/conversation-builder/scenes/' . $fakeScene->getUid())
            ->assertStatus(422);
    }

    public function testForceDeleteSceneByUidInUse()
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

        $scene = new Scene();
        $scene->setUid('different');

        $intent = new Intent(new Turn($scene));
        $intent->setTransition(new Transition('some_conv', $fakeScene->getUid(), null));

        IntentDataClient::shouldReceive('getIntentWithSceneTransition')
            ->once()
            ->with($fakeScene->getUid())
            ->andReturn(new IntentCollection([$intent]));

        ConversationDataClient::shouldReceive('updateIntent')
            ->once()
            ->with($intent)
            ->andReturnUsing(function (Intent $intent) {
                $intent->setTransition(null);
                return $intent;
            });

        $this->actingAs($this->user, 'api')
            ->json('DELETE', '/admin/api/conversation-builder/scenes/' . $fakeScene->getUid(), [
                'force' => true
            ])
            ->assertStatus(200);
    }

    public function testDeleteSceneByUidInUseBySelf()
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

        $intent = new Intent(new Turn($fakeScene));
        IntentDataClient::shouldReceive('getIntentWithSceneTransition')
            ->once()
            ->with($fakeScene->getUid())
            ->andReturn(new IntentCollection([$intent]));

        $this->actingAs($this->user, 'api')
            ->json('DELETE', '/admin/api/conversation-builder/scenes/' . $fakeScene->getUid())
            ->assertStatus(200);
    }

    public function testDuplication()
    {
        $scenario = ScenariosTest::getFakeScenarioForDuplication();

        /** @var Conversation $conversation */
        $conversation = $scenario->getConversations()->getObjectsWithId('example_conversation')->first();

        /** @var Scene $scene */
        $scene = $conversation->getScenes()->getObjectsWithId('example_scene')->first();

        // Called during route binding
        ConversationDataClient::shouldReceive('getSceneByUid')
            ->once()
            ->andReturn($scene);

        // Called in the controller, getting parent & sibling data
        ConversationDataClient::shouldReceive('getConversationByUid')
            ->once()
            ->andReturnUsing(function ($uid) use ($conversation) {
                return $conversation;
            });

        // Called in controller, once before persisting, again after, and finally after patching
        SceneDataClient::shouldReceive('getFullSceneGraph')
            ->times(3)
            ->andReturnUsing(function ($uid) use ($scene) {
                $scene->setUid($uid);
                return $scene;
            });

        SceneDataClient::shouldReceive('addFullSceneGraph')
            ->once()
            ->andReturnUsing(function ($scene) {
                $scene->setUid('0x9999');
                return $scene;
            });

        $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/conversation-builder/scenes/' . $scene->getUid() . '/duplicate')
            ->assertStatus(200)
            ->assertJson([
                'name' => 'Example Scene copy 2',
                'od_id' => 'example_scene_copy_2',
                'id'=> '0x9999',
            ]);
    }
}
