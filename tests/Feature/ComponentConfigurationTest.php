<?php

namespace Tests\Feature;

use App\User;
use OpenDialogAi\AttributeEngine\DynamicAttribute;
use OpenDialogAi\Core\Components\Configuration\ComponentConfiguration;
use OpenDialogAi\Core\InterpreterEngine\Luis\LuisInterpreterConfiguration;
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
        for ($i = 0; $i < 52; $i++) {
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

    public function testUpdate()
    {
        /** @var ComponentConfiguration $configuration */
        $configuration = factory(ComponentConfiguration::class)->create();

        $data = [
            'name' => 'My New Name',
            'component_id' => self::COMPONENT_ID,
            'configuration' => self::CONFIGURATION,
        ];

        $this->actingAs($this->user, 'api')
            ->json('PATCH', '/admin/api/component-configuration/'.$configuration->id, $data)
            ->assertNoContent();

        /** @var ComponentConfiguration $updatedConfiguration */
        $updatedConfiguration = ComponentConfiguration::find($configuration->id);

        $this->assertEquals($updatedConfiguration->name, $data['name']);
        $this->assertEquals($updatedConfiguration->componentId, $data['component_id']);
        $this->assertEquals($updatedConfiguration->configuration, $data['configuration']);
    }

    public function testUpdateDuplicateName()
    {
        /** @var ComponentConfiguration $a */
        $a = factory(ComponentConfiguration::class)->create();

        /** @var ComponentConfiguration $b */
        $a = factory(ComponentConfiguration::class)->create();

        $data = [
            'name' => $b->name
        ];

        $this->actingAs($this->user, 'api')
            ->json('PATCH', '/admin/api/component-configuration/'.$a->id, $data)
            ->assertStatus(400)
            ->assertJsonFragment([
                'field' => 'name',
            ]);
    }

    public function testStoreValidData()
    {
        $data = [
            'name' => 'My New Name',
            'component_id' => self::COMPONENT_ID,
            'configuration' => self::CONFIGURATION,
        ];

        $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/component-configuration', $data)
            ->assertStatus(201)
            ->assertJsonFragment($data);

        $this->assertDatabaseHas('dynamic_attributes', $data);
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
            ->assertStatus(400)
            ->assertJson([
                'field' => 'component_id',
            ]);
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
            ->assertStatus(400)
            ->assertJson([
                'field' => 'configuration',
            ]);
    }

    public function testDestroy()
    {
        /** @var ComponentConfiguration $configuration */
        $configuration = factory(ComponentConfiguration::class)->create();

        $this->actingAs($this->user, 'api')
            ->json('DELETE', '/admin/api/component-configuration/'.$configuration->id)
            ->assertStatus(204);

        $this->assertEquals(null, DynamicAttribute::find($configuration->id));
    }
}
