<?php

namespace Tests\Feature;

use App\User;
use DateTime;
use Illuminate\Support\Facades\Artisan;
use Mockery\MockInterface;
use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;
use OpenDialogAi\Core\Components\Configuration\ComponentConfiguration;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\IntentCollection;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\ScenarioCollection;
use OpenDialogAi\Core\InterpreterEngine\Callback\CallbackInterpreterConfiguration;
use OpenDialogAi\Core\InterpreterEngine\Luis\LuisInterpreterConfiguration;
use OpenDialogAi\Core\InterpreterEngine\Service\ConfiguredInterpreterServiceInterface;
use OpenDialogAi\InterpreterEngine\Interpreters\CallbackInterpreter;
use OpenDialogAi\InterpreterEngine\Service\InterpreterComponentServiceInterface;
use Tests\TestCase;

class ComponentConfigurationTest extends TestCase
{
    const COMPONENT_ID = 'interpreter.core.callbackInterpreter';
    const CONFIGURATION = [
        'callbacks' => [
            'WELCOME' => 'intent.core.welcome',
        ],
    ];

    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        Artisan::call('configurations:create');

        $this->app->forgetInstance(ConfiguredInterpreterServiceInterface::class);
        $this->app->forgetInstance(InterpreterComponentServiceInterface::class);

        $this->user = factory(User::class)->create();
    }

    public function testView()
    {
        /** @var ComponentConfiguration $configuration */
        $configuration = factory(ComponentConfiguration::class)->create();

        $this->get('/admin/api/component-configuration/'.$configuration->id)
            ->assertStatus(302);

        $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/component-configuration/'.$configuration->id)
            ->assertStatus(200)
            ->assertJsonFragment([
                'component_id' => self::COMPONENT_ID,
                'configuration' => self::CONFIGURATION
            ]);
    }

    public function testViewAll()
    {
        for ($i = 0; $i < 51; $i++) {
            factory(ComponentConfiguration::class)->create();
        }

        $configurations = ComponentConfiguration::all();

        $this->get('/admin/api/component-configuration')
            ->assertStatus(302);

        $response = $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/component-configuration?page=1')
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    $configurations[0]->toArray(),
                    $configurations[1]->toArray(),
                    $configurations[2]->toArray(),
                ],
            ])
            ->getData();

        $this->assertEquals(50, count($response->data));

        $response = $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/component-configuration?page=2')
            ->assertStatus(200)
            ->getData();

        $this->assertEquals(2, count($response->data));
    }

    public function testViewAllByComponentType()
    {
        factory(ComponentConfiguration::class)->create([
            'component_id' => 'interpreter.test.one',
        ]);

        factory(ComponentConfiguration::class)->create([
            'component_id' => 'action.test.one',
        ]);

        factory(ComponentConfiguration::class)->create([
            'component_id' => 'action.test.two',
        ]);

        $configurations = ComponentConfiguration::all();

        $this->get('/admin/api/component-configuration?type=interpreter')
            ->assertStatus(302);

        $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/component-configuration?type=action')
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    $configurations[2]->toArray(),
                    $configurations[3]->toArray(),
                ],
            ]);
    }

    public function testUpdate()
    {
        /** @var ComponentConfiguration $configuration */
        $configuration = factory(ComponentConfiguration::class)->create();

        $data = [
            'name' => 'My New Name',
        ];

        $this->actingAs($this->user, 'api')
            ->json('PATCH', '/admin/api/component-configuration/'.$configuration->id, $data)
            ->assertNoContent();

        /** @var ComponentConfiguration $updatedConfiguration */
        $updatedConfiguration = ComponentConfiguration::find($configuration->id);

        $this->assertEquals($data['name'], $updatedConfiguration->name);
        $this->assertEquals(self::COMPONENT_ID, $updatedConfiguration->component_id);
        $this->assertEquals(self::CONFIGURATION, $updatedConfiguration->configuration);
    }

    public function testUpdateDuplicateName()
    {
        /** @var ComponentConfiguration $a */
        $a = factory(ComponentConfiguration::class)->create();

        /** @var ComponentConfiguration $b */
        $b = factory(ComponentConfiguration::class)->create();

        $data = [
            'name' => $b->name
        ];

        $this->actingAs($this->user, 'api')
            ->json('PATCH', '/admin/api/component-configuration/'.$a->id, $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function testStoreValidData()
    {
        $name = 'My New Name';
        $data = [
            'name' => $name,
            'component_id' => self::COMPONENT_ID,
            'configuration' => self::CONFIGURATION,
        ];

        $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/component-configuration', $data)
            ->assertStatus(201)
            ->assertJsonFragment($data);

        $this->assertDatabaseHas('component_configurations', ['name' => $name]);
    }

    public function testStoreInvalidComponentId()
    {
        $data = [
            'name' => 'My New Name',
            'component_id' => 'unknown',
            'configuration' => [],
        ];

        $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/component-configuration/', $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['component_id']);
    }

    public function testStoreMissingName()
    {
        $data = [
            'component_id' => self::COMPONENT_ID,
            'configuration' => self::CONFIGURATION,
        ];

        $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/component-configuration/', $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function testStoreInvalidConfiguration()
    {
        // LUIS interpreter requires three fields in its configuration (app_url, app_id, subscription_key)

        $data = [
            'name' => 'My New Name',
            'component_id' => 'interpreter.core.luis',
            'configuration' => [
                LuisInterpreterConfiguration::APP_URL => 'https://example.com/',
                LuisInterpreterConfiguration::APP_ID => '123',
            ],
        ];

        $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/component-configuration/', $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['configuration']);
    }

    public function testStoreInvalidAppUrl()
    {
        $data = [
            'name' => 'My New Name',
            'component_id' => 'interpreter.core.luis',
            'configuration' => [
                LuisInterpreterConfiguration::APP_URL => 'file://example.com/', //invalid scheme
                LuisInterpreterConfiguration::APP_ID => '123',
            ],
        ];

        $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/component-configuration/', $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['configuration.app_url']);
    }

    public function testStoreInvalidWebhookUrl()
    {
        $data = [
            'name' => 'Bad webhook',
            'component_id' => 'action.core.webhook',
            'configuration' => [
                'webhook_url' => 'localhost'
            ],
        ];

        $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/component-configuration/', $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['configuration.webhook_url']);
    }

    public function testDestroy()
    {
        /** @var ComponentConfiguration $configuration */
        $configuration = factory(ComponentConfiguration::class)->create();

        $this->actingAs($this->user, 'api')
            ->json('DELETE', '/admin/api/component-configuration/'.$configuration->id)
            ->assertStatus(204);

        $this->assertEquals(null, ComponentConfiguration::find($configuration->id));
    }

    public function testTestConfigurationSuccess()
    {
        $data = [
            'name' => 'My New Name',
            'component_id' => self::COMPONENT_ID,
            'configuration' => self::CONFIGURATION,
        ];

        $this->mock(InterpreterComponentServiceInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('get')
                ->twice()
                ->andReturn(CallbackInterpreter::class);

            // For request validation
            $mock->shouldReceive('has')
                ->once()
                ->andReturn(true);
        });

        $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/component-configurations/test', $data)
            ->assertStatus(200);
    }

    public function testTestConfigurationFailureInvalidData()
    {
        $data = [
            'name' => 'My New Name',
            'configuration' => self::CONFIGURATION,
        ];

        $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/component-configurations/test', $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['component_id']);
    }

    public function testTestConfigurationFailureInvalidUrl()
    {
        $data = [
            'name' => 'My New Name',
            'component_id' => 'interpreter.core.luis',
            'configuration' => [
                LuisInterpreterConfiguration::APP_URL => 'file://example.com/', //invalid scheme
                LuisInterpreterConfiguration::APP_ID => '123',
            ],
        ];

        $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/component-configurations/test', $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['configuration.app_url']);
    }

    public function testTestConfigurationFailureNoResponseFromProvider()
    {
        $data = [
            'name' => 'My New Name',
            'component_id' => self::COMPONENT_ID,
            'configuration' => self::CONFIGURATION,
        ];

        $mockInterpreter = new class(CallbackInterpreterConfiguration::create('test', self::CONFIGURATION)) extends CallbackInterpreter {
            public function interpret(UtteranceAttribute $utterance): IntentCollection
            {
                return new IntentCollection();
            }
        };

        $this->mock(InterpreterComponentServiceInterface::class, function (MockInterface $mock) use ($mockInterpreter) {
            $mock->shouldReceive('get')
                ->twice()
                ->andReturn(get_class($mockInterpreter));

            $mock->shouldReceive('has')
                ->once()
                ->andReturn(true);
        });

        $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/component-configurations/test', $data)
            ->assertStatus(400);
    }

    public function testQueryConfigurationUse()
    {
        $configurationName = 'Default Callback';
        $data = [
            'name' => $configurationName,
        ];

        $scenario1 = new Scenario();
        $scenario1->setUid('0x123');
        $scenario1->setOdId('scenario_1');
        $scenario1->setInterpreter($configurationName);
        $scenario1->setCreatedAt(new DateTime());
        $scenario1->setUpdatedAt(new DateTime());

        $scenario2 = new Scenario();
        $scenario2->setUid('0x456');
        $scenario2->setOdId('scenario_2');
        $scenario2->setInterpreter($configurationName);
        $scenario2->setCreatedAt(new DateTime());
        $scenario2->setUpdatedAt(new DateTime());

        ConversationDataClient::shouldReceive('getScenariosWhereInterpreterIsUsed')
            ->once()
            ->andReturn(new ScenarioCollection([
                $scenario1,
                $scenario2,
            ]));

        $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/component-configurations/query', $data)
            ->assertStatus(200);
    }
}
