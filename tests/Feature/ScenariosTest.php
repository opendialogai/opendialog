<?php


namespace Tests\Feature;

use App\User;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use App\Http\Facades\Serializer;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\ScenarioCollection;

use Tests\TestCase;

class ScenariosTest extends TestCase
{
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
    }

    public function testGetScenariosRequiresAuthentication()
    {
        $this->get('/admin/api/conversation-builder/scenarios')
            ->assertStatus(302);
    }

    public function testGetScenarios()
    {
        $fakeScenario1 = new Scenario();
        $fakeScenario1->setName("Example scenario");
        $fakeScenario1->setUid('0x0001');
        $fakeScenario1->setODId('example_scenario');

        $fakeScenario2 = new Scenario();
        $fakeScenario2->setName("Example scenario");
        $fakeScenario2->setUid('0x0001');
        $fakeScenario2->setODId('example_scenario');

        $fakeScenarioCollection = new ScenarioCollection();
        $fakeScenarioCollection->addObject($fakeScenario1);
        $fakeScenarioCollection->addObject($fakeScenario2);

        ConversationDataClient::shouldReceive('getAllScenarios')
            ->once()
            ->andReturn($fakeScenarioCollection);

        Serializer::shouldReceive('serialize')
            ->once()
            ->with($fakeScenarioCollection, 'json')
            ->andReturn('[
            {
            "uid": "0x0001",
            "odId": "example_scenario1",
            "name": "Example scenario1",
            "description": "An example scenario",
            "updatedAt": "2021-02-25T14:30:00.000Z",
            "createdAt": "2021-02-24T09:30:00.000Z",
            "defaultInterpreter": "interpreter.core.nlp",
            "behaviors": [],
            "conditions": [],
            "status": "PUBLISHED",
            "conversations": ["0x0002"]
        },
        {
            "uid": "0x0002",
            "odId": "example_scenario2",
            "name": "Example scenario2",
            "description": "An example scenario",
            "updatedAt": "2021-02-25T14:30:00.000Z",
            "createdAt": "2021-02-24T09:30:00.000Z",
            "defaultInterpreter": "interpreter.core.nlp",
            "behaviors": [],
            "conditions": [],
            "status": "PUBLISHED",
            "conversations": ["0x0002"]
        }]');


        $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/conversation-builder/scenarios')
            ->assertStatus(200)
            ->assertJson([[
                "uid"=> "0x0001",
                "odId"=> "example_scenario1",
                "name"=> "Example scenario1",
                ],
                [
                "uid"=> "0x0002",
                "odId"=> "example_scenario2",
                "name"=> "Example scenario2"
                ]]);
    }

    public function testGetScenarioByUid()
    {
        $fakeScenario = new Scenario();
        $fakeScenario->setName("Example scenario");
        $fakeScenario->setUid('0x0001');
        $fakeScenario->setODId('example_scenario');

        Serializer::shouldReceive('serialize')
            ->once()
            ->with($fakeScenario, 'json')
            ->andReturn('{
            "uid": "0x0001",
            "odId": "example_scenario",
            "name": "Example scenario",
            "description": "An example scenario",
            "updatedAt": "2021-02-25T14:30:00.000Z",
            "createdAt": "2021-02-24T09:30:00.000Z",
            "defaultInterpreter": "interpreter.core.nlp",
            "behaviors": [],
            "conditions": [],
            "status": "PUBLISHED",
            "conversations": ["0x0002"]
        }');

        ConversationDataClient::shouldReceive('getScenarioByUid')
            ->once()
            ->with($fakeScenario->getUid())
            ->andReturn($fakeScenario);

        $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/conversation-builder/scenarios/' . $fakeScenario->getUid())
            ->assertJson([
                'name' => 'Example scenario',
                'uid' => '0x0001',
                'odId' => 'example_scenario',
                'description' =>  'An example scenario'
            ]);
    }

    public function testCreateNewScenario()
    {
        $fakeScenario = new Scenario();
        $fakeScenario->setName("Example scenario");
        $fakeScenario->setODId("example_scenario");
        $fakeScenario->setDescription('An example scenario');

        $fakeScenarioCreated = new Scenario();
        $fakeScenarioCreated->setName("Example scenario");
        $fakeScenarioCreated->setUid("0x0001");
        $fakeScenarioCreated->setODId("example_scenario");
        $fakeScenarioCreated->setDescription('An example scenario');

        Serializer::shouldReceive('deserialize')
            ->once()
            ->andReturn($fakeScenario);

        Serializer::shouldReceive('serialize')
            ->once()
            ->with($fakeScenarioCreated, 'json')
            ->andReturn('{
            "uid": "0x0001",
            "odId": "example_scenario",
            "name": "Example scenario",
            "description": "An example scenario"
        }');

        ConversationDataClient::shouldReceive('addScenario')
            ->once()
            ->with($fakeScenario)
            ->andReturn($fakeScenarioCreated);

        $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/conversation-builder/scenarios/', [
                'name' => 'Example scenario',
                'odId' => 'example_scenario',
                'description' =>  'An example scenario'
            ])
            ->assertStatus(201)
            ->assertJson([
                'name' => 'Example scenario',
                'uid'=> '0x0001',
                'odId' => 'example_scenario',
                'description' =>  'An example scenario'
            ]);
    }


    public function testUpdateScenario()
    {
        $fakeScenarioUpdated = new Scenario();
        $fakeScenarioUpdated->setName("Example scenario updated");
        $fakeScenarioUpdated->setUid("0x0001");
        $fakeScenarioUpdated->setODId("example_scenario");
        $fakeScenarioUpdated->setDescription('An example scenario updated');

        Serializer::shouldReceive('deserialize')
            ->once()
            ->andReturn($fakeScenarioUpdated);

        Serializer::shouldReceive('serialize')
            ->once()
            ->with($fakeScenarioUpdated, 'json')
            ->andReturn('{
            "uid": "0x0001",
            "odId": "example_scenario",
            "name": "Example scenario updated",
            "description": "An example scenario updated"
        }');

        ConversationDataClient::shouldReceive('updateScenario')
            ->once()
            ->with($fakeScenarioUpdated)
            ->andReturn($fakeScenarioUpdated);

        $this->actingAs($this->user, 'api')
            ->json('PUT', '/admin/api/conversation-builder/scenarios/' . $fakeScenarioUpdated->getUid(), [
                'name' => $fakeScenarioUpdated->getName(),
                'uid' => $fakeScenarioUpdated->getUid(),
                'odId' => $fakeScenarioUpdated->getODId(),
                'description' =>  $fakeScenarioUpdated->getDescription()
            ])
            //->assertStatus(201)
            ->assertJson([
                'name' => 'Example scenario updated',
                'uid'=> '0x0001',
                'odId' => 'example_scenario',
                'description' =>  'An example scenario updated'
            ]);
    }

    public function testDeleteScenario()
    {
        $fakeScenario = new Scenario();
        $fakeScenario->setName("Example scenario");
        $fakeScenario->setUid('0x0001');
        $fakeScenario->setODId('example_scenario');

        ConversationDataClient::shouldReceive('getScenarioByUid')
            ->once()
            ->with($fakeScenario->getUid())
            ->andReturn($fakeScenario);

        ConversationDataClient::shouldReceive('deleteScenarioByUid')
            ->once()
            ->with($fakeScenario->getUid())
            ->andReturn(true);

        $this->actingAs($this->user, 'api')
            ->json('DELETE', '/admin/api/conversation-builder/scenarios/' . $fakeScenario->getUid())
            ->assertStatus(200);
    }
}
