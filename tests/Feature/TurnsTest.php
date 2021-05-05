<?php


namespace Tests\Feature;

use App\User;
use Carbon\Carbon;
use OpenDialogAi\Core\Conversation\BehaviorsCollection;
use OpenDialogAi\Core\Conversation\ConditionCollection;
use OpenDialogAi\Core\Conversation\Exceptions\ConversationObjectNotFoundException;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\IntentCollection;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\Turn;
use OpenDialogAi\Core\Conversation\TurnCollection;
use Tests\TestCase;

class TurnsTest extends TestCase
{
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
    }

    public function testGetTurnsRequiresAuthentication()
    {
        $this->get('/admin/api/conversation-builder/turns/trigger302')
            ->assertStatus(302);
    }

    public function testGetTurnNotFound()
    {
        ConversationDataClient::shouldReceive('getTurnByUid')
            ->once()
            ->with('test', false)
            ->andThrow(new ConversationObjectNotFoundException());

        $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/conversation-builder/turns/test')
            ->assertStatus(404);
    }


    public function testGetAllTurnsByScene()
    {

        $fakeScene = new Scene();
        $fakeScene->setUid('0x0002');
        $fakeScene->setName('New Example scene 1');
        $fakeScene->setOdId('new_example_scene_1');
        $fakeScene->setDescription("An new example scene 1");

        $fakeTurn1 = new Turn($fakeScene);
        $fakeTurn1->setUid('0x0003');
        $fakeTurn1->setOdId('welcome_scene_1');
        $fakeTurn1->setName('Welcome scene 1');
        $fakeTurn1->setDescription('A welcome scene 1');
        $fakeTurn1->setCreatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $fakeTurn1->setUpdatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $fakeTurn1->setInterpreter('interpreter.core.nlp');
        $fakeTurn1->setConditions(new ConditionCollection());
        $fakeTurn1->setBehaviors(new BehaviorsCollection());
        $fakeTurn1->setRequestIntents(new IntentCollection());
        $fakeTurn1->setResponseIntents(new IntentCollection());

        $fakeTurn2 = new Turn($fakeScene);
        $fakeTurn2->setUid('0x0004');
        $fakeTurn2->setOdId('welcome_scene_2');
        $fakeTurn2->setName('Welcome scene 2');
        $fakeTurn2->setDescription('A welcome scene 2');
        $fakeTurn2->setCreatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $fakeTurn2->setUpdatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $fakeTurn2->setInterpreter('interpreter.core.nlp');
        $fakeTurn2->setConditions(new ConditionCollection());
        $fakeTurn2->setBehaviors(new BehaviorsCollection());
        $fakeTurn2->setRequestIntents(new IntentCollection());
        $fakeTurn2->setResponseIntents(new IntentCollection());

        $fakeTurnCollection = new TurnCollection();
        $fakeTurnCollection->addObject($fakeTurn1);
        $fakeTurnCollection->addObject($fakeTurn2);

        ConversationDataClient::shouldReceive('getSceneByUid')
            ->once()
            ->with($fakeScene->getUid(), false)
            ->andReturn($fakeScene);

        ConversationDataClient::shouldReceive('getAllTurnsByScene')
            ->once()
            ->andReturn($fakeTurnCollection);

        $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/conversation-builder/scenes/' . $fakeScene->getUid() . '/turns')
//            ->assertStatus(200)
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
                "intents" => []
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
                "intents" => []
            ]]);
    }

    public function testAddTurnToScene()
    {
        $fakeScene = new Scene();
        $fakeScene->setUid('0x0003');
        $fakeScene->setName('New Example scene 1');
        $fakeScene->setOdId('new_example_scene_1');
        $fakeScene->setDescription("An new example scene 1");

        $fakeTurn = new Turn();
        $fakeTurn->setUid('0x0004');
        $fakeTurn->setOdId('welcome_turn');
        $fakeTurn->setName('Welcome turn');
        $fakeTurn->setDescription('A welcome turn');
        $fakeTurn->setCreatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $fakeTurn->setUpdatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $fakeTurn->setInterpreter('interpreter.core.nlp');
        $fakeTurn->setConditions(new ConditionCollection());
        $fakeTurn->setBehaviors(new BehaviorsCollection());
        $fakeTurn->setValidOrigins(['origin_a', 'origin_b']);
        $fakeTurn->setRequestIntents(new IntentCollection());
        $fakeTurn->setResponseIntents(new IntentCollection());

        ConversationDataClient::shouldReceive('addTurn')
            ->once()
            ->withAnyArgs()
            ->andReturn($fakeTurn);

        ConversationDataClient::shouldReceive('getSceneByUid')
            ->once()
            ->with($fakeScene->getUid(), false)
            ->andReturn($fakeScene);

        $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/conversation-builder/scenes/' . $fakeScene->getUid() . '/turns', [
                "od_id" => "welcome_turn",
                "name" => "Welcome turn",
                "description" => "A welcome turn",
                "default_interpreter" => "interpreter.core.nlp",
                "conditions" => [],
                "behaviors" => [],
                "valid_origins" => ["origin_a", "origin_b"],
            ])
            //->assertStatus(200)
            ->assertJson([
                "id" => "0x0004",
                "od_id" => "welcome_turn",
                "name" => "Welcome turn",
                "description" => "A welcome turn",
                "interpreter" => "interpreter.core.nlp",
                "created_at" => "2021-02-24T09:30:00+0000",
                "updated_at" => "2021-02-24T09:30:00+0000",
                "conditions" => [],
                "behaviors" => [],
                "valid_origins" => ["origin_a", "origin_b"],
                "intents" => []
            ]);
    }

    public function testGetTurnByUid()
    {
        $fakeTurn = new Turn();
        $fakeTurn->setUid('0x0001');
        $fakeTurn->setOdId('welcome_turn');
        $fakeTurn->setName('Welcome Turn');
        $fakeTurn->setDescription('A welcome turn');
        $fakeTurn->setInterpreter('interpreter.core.nlp');
        $fakeTurn->setBehaviors(new BehaviorsCollection());
        $fakeTurn->setConditions(new ConditionCollection());
        $fakeTurn->setCreatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $fakeTurn->setUpdatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $fakeTurn->setValidOrigins(['origin_a', 'origin_b']);
        $fakeTurn->setRequestIntents(new IntentCollection());
        $fakeTurn->setResponseIntents(new IntentCollection());

        ConversationDataClient::shouldReceive('getTurnByUid')
            ->once()
            ->with($fakeTurn->getUid(), false)
            ->andReturn($fakeTurn);

        $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/conversation-builder/turns/' . $fakeTurn->getUid())
            ->assertExactJson([
                "id" => "0x0001",
                "od_id" => "welcome_turn",
                "name" => "Welcome Turn",
                "description" => "A welcome turn",
                "interpreter" => "interpreter.core.nlp",
                "created_at" => "2021-02-24T09:30:00+0000",
                "updated_at" => "2021-02-24T09:30:00+0000",
                "conditions" => [],
                "behaviors" => [],
                "valid_origins" => ['origin_a', 'origin_b'],
                "intents" => [],
            ]);
    }

    public function testUpdateTurnByUid()
    {
        $fakeTurn = new Turn();
        $fakeTurn->setUid('0x0004');
        $fakeTurn->setOdId('welcome_turn');
        $fakeTurn->setName('Welcome turn');
        $fakeTurn->setDescription('A welcome turn');
        $fakeTurn->setInterpreter('interpreter.core.nlp');
        $fakeTurn->setBehaviors(new BehaviorsCollection());
        $fakeTurn->setConditions(new ConditionCollection());
        $fakeTurn->setCreatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $fakeTurn->setUpdatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $fakeTurn->setValidOrigins(['origin_a', 'origin_b']);
        $fakeTurn->setRequestIntents(new IntentCollection());
        $fakeTurn->setResponseIntents(new IntentCollection());

        $fakeTurnUpdated = new Turn();
        $fakeTurnUpdated->setUid('0x0004');
        $fakeTurnUpdated->setOdId('welcome_turn_updated');
        $fakeTurnUpdated->setName('Welcome turn updated');
        $fakeTurnUpdated->setDescription('A welcome turn updated');
        $fakeTurnUpdated->setInterpreter('interpreter.core.nlp');
        $fakeTurnUpdated->setBehaviors(new BehaviorsCollection());
        $fakeTurnUpdated->setConditions(new ConditionCollection());
        $fakeTurnUpdated->setCreatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $fakeTurnUpdated->setUpdatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $fakeTurnUpdated->setValidOrigins(['origin_a_updated', 'origin_b_updated', 'origin_c']);
        $fakeTurnUpdated->setRequestIntents(new IntentCollection());
        $fakeTurnUpdated->setResponseIntents(new IntentCollection());

        ConversationDataClient::shouldReceive('getTurnByUid')
            ->once()
            ->with($fakeTurn->getUid(), false)
            ->andReturn($fakeTurn);

        ConversationDataClient::shouldReceive('updateTurn')
            ->once()
            ->withAnyArgs()
            ->andReturn($fakeTurnUpdated);

        $this->actingAs($this->user, 'api')
            ->json('PATCH', '/admin/api/conversation-builder/turns/' . $fakeTurn->getUid(), [
                'name' => $fakeTurnUpdated->getName(),
                'id' => $fakeTurnUpdated->getUid(),
                'description' =>  $fakeTurnUpdated->getDescription(),
                'valid_origins' => $fakeTurnUpdated->getValidOrigins()
            ])
            //->assertStatus(200)
            ->assertJson([
                "id" => "0x0004",
                "name" => "Welcome turn updated",
                "description" => "A welcome turn updated",
                "interpreter" => "interpreter.core.nlp",
                "created_at" => "2021-02-24T09:30:00+0000",
                "updated_at" => "2021-02-24T09:30:00+0000",
                "conditions" => [],
                "behaviors" => [],
                "valid_origins" => ["origin_a_updated", "origin_b_updated", "origin_c"],
                "intents" => [],
            ]);
    }

    public function testDeleteTurnByUid()
    {
        $fakeTurn = new Turn();
        $fakeTurn->setUid('0x0001');
        $fakeTurn->setOdId('welcome_turn');
        $fakeTurn->setName('Welcome Turn');
        $fakeTurn->setDescription('A welcome turn');

        ConversationDataClient::shouldReceive('getTurnByUid')
            ->once()
            ->with($fakeTurn->getUid(), false)
            ->andReturn($fakeTurn);

        ConversationDataClient::shouldReceive('deleteTurnByUid')
            ->once()
            ->with($fakeTurn->getUid())
            ->andReturn(true);

        $this->actingAs($this->user, 'api')
            ->json('DELETE', '/admin/api/conversation-builder/turns/' . $fakeTurn->getUid())
            ->assertStatus(200);
    }
}
