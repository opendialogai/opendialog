<?php

namespace Tests\Feature;

use App\User;
use Ds\Map;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use OpenDialogAi\AttributeEngine\DynamicAttribute;
use OpenDialogAi\AttributeEngine\Facades\AttributeResolver;
use Tests\TestCase;

/**
 * Class DynamicAttributesTest
 *
 * @package Tests\Feature
 * @group SpecificationTests
 */
class DynamicAttributesTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    const INVALID_IDS = [
        'testDynamicAttribute',
        'test_Dynamic.Attribute',
        'test_dynamic_attribute_1',
        'test_Dynamic.Attribute_1',
        'testdynamicattribute1',
    ];

    const INVALID_TYPES = [
        'attributeCoreString',
        'attribute.string',
        'core.string',
        'string',
        'wrong.core.string'
    ];

    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
    }

    public function testView()
    {
        /* @var $dynamicAttribute DynamicAttribute */
        $dynamicAttribute = factory(DynamicAttribute::class)->create();

        $this->get('/admin/api/dynamic-attribute/'.$dynamicAttribute->id)->assertStatus(302);

        $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/dynamic-attribute/'.$dynamicAttribute->id)
            ->assertStatus(200)->assertJsonFragment([
                'attribute_id' => $dynamicAttribute->attribute_id,
                'attribute_type' => $dynamicAttribute->attribute_type
            ]);
    }

    public function testViewAll()
    {
        for ($i = 0; $i < 52; $i++) {
            factory(DynamicAttribute::class)->create();
        }
        $dynamicAttributes = DynamicAttribute::all();

        $this->get('/admin/api/dynamic-attribute')->assertStatus(302);

        $response = $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/dynamic-attribute?page=1')
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    $dynamicAttributes[0]->toArray(),
                    $dynamicAttributes[1]->toArray(),
                    $dynamicAttributes[2]->toArray(),
                ],
            ])->getData();

        $this->assertEquals(count($response->data), 50);

        $response = $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/dynamic-attribute?page=2')
            ->assertStatus(200)->getData();

        $this->assertEquals(count($response->data), 2);
    }

    public function testUpdateValidData()
    {
        $dynamicAttribute = factory(DynamicAttribute::class)->create();
        $data = [
            'attribute_id' => 'updated_id', 'attribute_type' => 'attribute.core.string'
        ];

        $this->actingAs($this->user, 'api')
            ->json('PATCH', '/admin/api/dynamic-attribute/'.$dynamicAttribute->id, $data)
            ->assertNoContent(204);

        $updatedDynamicAttribute = DynamicAttribute::find($dynamicAttribute->id);

        $this->assertEquals($updatedDynamicAttribute->attribute_id, $data['attribute_id']);
        $this->assertEquals($updatedDynamicAttribute->attribute_type, $data['attribute_type']);
    }

    public function testUpdateIdOnly()
    {
        $dynamicAttribute = DynamicAttribute::create([
            'attribute_id' => 'test_dynamic_attribute',
            'attribute_type' => 'attribute.core.int'
        ]);

        $data = [
            'attribute_id' => 'updated_id',
        ];

        $this->actingAs($this->user, 'api')
            ->json('PATCH', '/admin/api/dynamic-attribute/'.$dynamicAttribute->id, $data)
            ->assertStatus(204);

        $updatedDynamicAttribute = DynamicAttribute::find($dynamicAttribute->id);

        $this->assertEquals($updatedDynamicAttribute->attribute_id, $data['attribute_id']);
        $this->assertEquals($updatedDynamicAttribute->attribute_type, $dynamicAttribute->attribute_type);
    }

    public function testUpdateTypeOnly()
    {
        $dynamicAttribute = factory(DynamicAttribute::class)->create();

        $typeOnly = [
            'attribute_type' => 'attribute.core.int'
        ];

        $this->actingAs($this->user, 'api')
            ->json('PATCH', '/admin/api/dynamic-attribute/'.$dynamicAttribute->id, $typeOnly)
            ->assertStatus(204);

        $updatedDynamicAttribute = DynamicAttribute::find($dynamicAttribute->id);

        $this->assertEquals($updatedDynamicAttribute->attribute_id, $dynamicAttribute->attribute_id);
        $this->assertEquals($updatedDynamicAttribute->attribute_type, $typeOnly['attribute_type']);
    }

    public function testUpdateDuplicateDynamicId()
    {
        $a = DynamicAttribute::create(['attribute_id' => 'test_dynamic_attribute_a', 'attribute_type' => 'attribute.core.int']);
        $b = DynamicAttribute::create(['attribute_id' => 'test_dynamic_attribute_b', 'attribute_type' => 'attribute.core.int']);

        $data = [
            'attribute_id' => $b->attribute_id
        ];

        $this->actingAs($this->user, 'api')
            ->json('PATCH', '/admin/api/dynamic-attribute/'.$a->id, $data)
            ->assertStatus(400)
            ->assertJson([
                'field' => 'attribute_id',
                'message' => sprintf("Attribute id '%s' is already in use.", $b->attribute_id)
            ]);
    }

    public function testUpdateDuplicateCoreId()
    {
        $dynamicAttribute = factory(DynamicAttribute::class)->create();

        $data = [
            'attribute_id' => 'name',
            'attribute_type' => 'attribute.core.string'
        ];

        $this->actingAs($this->user, 'api')
            ->json('PATCH', '/admin/api/dynamic-attribute/'.$dynamicAttribute->id, $data)
            ->assertStatus(400)
            ->assertJson([
                'field' => 'attribute_id',
                'message' => sprintf("Attribute id '%s' is already in use.", $data['attribute_id'])
            ]);
    }

    public function testUpdateSameId()
    {
        $data = [
            'attribute_id' => 'test_dynamic_attribute',
            'attribute_type' => 'attribute.core.string'
        ];

        $dynamicAttribute = DynamicAttribute::create($data);

        $this->actingAs($this->user, 'api')
            ->json('PATCH', '/admin/api/dynamic-attribute/'. $dynamicAttribute->id, $data)
            ->assertStatus(204);
    }

    public function testUpdateInvalidIdFormat()
    {
        $dynamicAttribute = factory(DynamicAttribute::class)->create();

        foreach (self::INVALID_IDS as $invalidId) {
            $data = [
                'attribute_id' => $invalidId
            ];

            $this->actingAs($this->user, 'api')
                ->json('PATCH', '/admin/api/dynamic-attribute/'.$dynamicAttribute->id, $data)
                ->assertStatus(400)
                ->assertJson([
                    'field' => 'attribute_id', 'message' => 'attribute_id field must follow snake_case format.',
                ]);
        }
    }

    public function testUpdateInvalidTypeFormat()
    {
        $dynamicAttribute = factory(DynamicAttribute::class)->create();

        foreach (self::INVALID_TYPES as $invalidType) {
            $data = [
                'attribute_type' => $invalidType
            ];

            $this->actingAs($this->user, 'api')
                ->json('PATCH', '/admin/api/dynamic-attribute/'.$dynamicAttribute->id, $data)
                ->assertStatus(400)
                ->assertJson([
                    'field' => 'attribute_type',
                    'message' => "attribute_type field must follow the format: 'attribute.<component_name>.<type>'",
                ]);
        }
    }

    public function testUpdateUnregisteredType()
    {
        $dynamicAttribute = factory(DynamicAttribute::class)->create();

        $data = [
            'attribute_type' => 'attribute.core.unregistered_attribute_type'
        ];

        $this->actingAs($this->user, 'api')
            ->json('PATCH', '/admin/api/dynamic-attribute/'.$dynamicAttribute->id, $data)
            ->assertStatus(400)
            ->assertJson([
                'field' => 'attribute_type',
                'message' => sprintf('attribute_type %s is not registered.', $data['attribute_type'])
            ]);
    }


    public function testStoreValidData()
    {
        $data = [
            'attribute_id' => 'test_dynamic_attribute', 'attribute_type' => 'attribute.core.string'
        ];

        $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/dynamic-attribute', $data)
            ->assertStatus(201)
            ->assertJsonFragment($data);

        $this->assertDatabaseHas('dynamic_attributes', $data);
    }

    public function testStoreDuplicateCoreId()
    {
        $data = [
            'attribute_id' => 'name',
            'attribute_type' => 'attribute.core.string'
        ];

        $this->actingAs($this->user, 'api')->json('POST', '/admin/api/dynamic-attribute/', $data)
            ->assertStatus(400)
            ->assertJson([
                'field' => 'attribute_id',
                'message' => sprintf("Attribute id '%s' is already in use.", $data['attribute_id'])
            ]);
    }

    public function testStoreDuplicateDynamicId()
    {
        $data = [
            'attribute_id' => 'test_dynamic_attribute',
            'attribute_type' => 'attribute.core.int'
        ];

        DynamicAttribute::create($data);

        $this->actingAs($this->user, 'api')->json('POST', '/admin/api/dynamic-attribute/', $data)
            ->assertStatus(400)
            ->assertJson([
                'field' => 'attribute_id',
                'message' => sprintf("Attribute id '%s' is already in use.", $data['attribute_id'] )
            ]);
    }

    public function testStoreMissingAttributeId()
    {
        $data = [
            'attribute_type' => 'attribute.core.string',
        ];

        $this->actingAs($this->user, 'api')->json('POST', '/admin/api/dynamic-attribute', $data)
            ->assertStatus(400)
            ->assertJsonFragment([
                'field' => 'attribute_id', 'message' => 'attribute_id field is required.',
            ]);

        $this->assertDatabaseMissing('dynamic_attributes', []);
    }

    public function testStoreMissingType()
    {
        $data = [
            'attribute_id' => 'test_dynamic_attribute'
        ];

        $this->actingAs($this->user, 'api')->json('POST', '/admin/api/dynamic-attribute', $data)
            ->assertStatus(400)
            ->assertJsonFragment([
                'field' => 'attribute_type', 'message' => 'attribute_type field is required.',
            ]);

        $this->assertDatabaseMissing('dynamic_attributes', []);
    }

    public function testStoreInvalidIdFormat()
    {
        foreach (self::INVALID_IDS as $id) {
            $data = [
                'attribute_id' => $id, 'attribute_type' => 'attribute.core.string'
            ];

            $this->actingAs($this->user, 'api')->json('POST', '/admin/api/dynamic-attribute', $data)
                ->assertStatus(400)
                ->assertJsonFragment([
                    'field' => 'attribute_id', 'message' => 'attribute_id field must follow snake_case format.',
                ]);

            $this->assertDatabaseMissing('dynamic_attributes', $data);
        }
    }

    public function testStoreInvalidTypeFormat()
    {
        foreach (self::INVALID_TYPES as $type) {
            $data = [
                'attribute_id' => 'test_dynamic_attribute', 'attribute_type' => $type
            ];

            $this->actingAs($this->user, 'api')->json('POST', '/admin/api/dynamic-attribute', $data)
                ->assertStatus(400)
                ->assertJsonFragment([
                    'field' => 'attribute_type',
                    'message' => "attribute_type field must follow the format: 'attribute.<component_name>.<type>'",
                ]);

            $this->assertDatabaseMissing('dynamic_attributes', $data);
        }
    }

    public function testStoreUnregisteredType()
    {
        $dynamicAttribute = factory(DynamicAttribute::class)->create();

        $data = [
            'attribute_id' => 'test_dynamic_attribute', 'attribute_type' => 'attribute.core.unregistered_attribute_type'
        ];

        $this->actingAs($this->user, 'api')->json('PATCH', '/admin/api/dynamic-attribute/'.$dynamicAttribute->id, $data)
            ->assertStatus(400)
            ->assertJson([
                'field' => 'attribute_type',
                'message' => sprintf('attribute_type %s is not registered.', $data['attribute_type'])
            ]);

        $this->assertDatabaseMissing('dynamic_attributes', $data);
    }

    public function testDestroy()
    {
        $dynamicAttribute = factory(DynamicAttribute::class)->create();

        $this->actingAs($this->user, 'api')
            ->json('DELETE', '/admin/api/dynamic-attribute/'.$dynamicAttribute->id)
            ->assertStatus(204);

        $this->assertEquals(DynamicAttribute::find($dynamicAttribute->id), null);
    }


    public function testDownload()
    {
        for ($i = 0; $i < 52; $i++) {
            factory(DynamicAttribute::class)->create();
        }
        $data = DynamicAttribute::all();

        $expected = [];
        foreach ($data as $datum) {
            $expected[$datum->attribute_id] = $datum->attribute_type;
        }

        $this->actingAs($this->user, 'api')
            ->get('/admin/api/dynamic-attributes/download')
            ->assertStatus(200)
            ->assertJson($expected);
    }

    public function testUploadNoAuth()
    {
        $data = [
            'dynamic_attributes_test_a' => 'attribute.core.string', 'dynamic_attributes_test_b' => 'attribute.core.int',
            'dynamic_attributes_test_c' => 'attribute.core.err'
        ];
        $this->post('/admin/api/dynamic-attributes/upload', $data)->assertStatus(302);
    }

    public function testUploadValidData()
    {
        $data = [
            'dynamic_attributes_test_a' => 'attribute.core.string', 'dynamic_attributes_test_b' => 'attribute.core.int',
            'dynamic_attributes_test_c' => 'attribute.core.int'
        ];
        $this->actingAs($this->user, 'api')->post('/admin/api/dynamic-attributes/upload', $data)
            ->assertStatus(201)->assertJson
            ($data);
        foreach ($data as $id => $type) {
            $this->assertDatabaseHas('dynamic_attributes', ['attribute_id' => $id, 'attribute_type' => $type]);
        }
    }

    public function testUploadDuplicateId()
    {
        $data = [
            'dynamic_attributes_test_a' => 'attribute.core.string', 'dynamic_attributes_test_b' => 'attribute.core.int',
            'dynamic_attributes_test_c' => 'attribute.core.int'
        ];
        foreach ($data as $attribute_id => $attribute_type) {
            DynamicAttribute::create(['attribute_id' => $attribute_id, 'attribute_type' => $attribute_type]);
        }

        $this->actingAs($this->user, 'api')->post('/admin/api/dynamic-attributes/upload', $data)
            ->assertStatus(400)
            ->assertJson([
                'ids' => array_keys($data), 'message' => 'Some ids are already in use.',
            ]);

    }

    public function testUploadEmptyData()
    {
        $noData = [];
        $this->actingAs($this->user, 'api')->post('/admin/api/dynamic-attributes/upload', $noData)
            ->assertStatus(400
            )->assertJson(

            [
                'message' => 'The provided JSON contains no properties. You must provide JSON object of the form:
 { <attribute_id>: attribute.<component>.<attribute_type>, ... }'
            ]);
    }

    public function testUploadInvalidIdFormats()
    {
        $data = array_fill_keys(self::INVALID_IDS, 'attribute.core.string');

        $this->actingAs($this->user, 'api')->post('/admin/api/dynamic-attributes/upload', $data)
            ->assertStatus(400)
            ->assertJson([
            'ids' => self::INVALID_IDS, 'message' => 'Invalid attribute IDs. All attribute IDs must be in snake_case.',
        ]);
        foreach (self::INVALID_IDS as $id) {
            $this->assertDatabaseMissing('dynamic_attributes',
                ['attribute_id' => $id, 'attribute_type' => 'attribute.core.string']);
        }
    }

    public function testUploadInvalidTypeFormats()
    {
        $ids = array_map(fn(
        ) => $this->faker->unique()->regexify
        (AttributeResolver::getValidIdPattern()),
            array_keys(self::INVALID_TYPES));
        $data = array_combine($ids, self::INVALID_TYPES);

        $this->actingAs($this->user, 'api')->post('/admin/api/dynamic-attributes/upload', $data)
            ->assertStatus(400)
            ->assertJson([
            'types' => self::INVALID_TYPES, 'message' => 'Invalid attribute types. All attribute types must be in the following format:
 attribute.<component>.<type>',
        ]);
        foreach ($data as $attribute_id => $attribute_type) {
            $this->assertDatabaseMissing('dynamic_attributes',
                ['attribute_id' => $attribute_id, 'attribute_type' => $attribute_type]);
        }
    }

    public function testUploadDuplicateCoreIds()
    {
        /** @var Map $attributes */
        $attributes = AttributeResolver::getSupportedAttributes();

        $ids = array_slice($attributes->keys()->toArray(), 0, 3);

        $data = array_fill_keys($ids, 'attribute.core.int');
        $this->actingAs($this->user, 'api')->post('/admin/api/dynamic-attributes/upload', $data)
            ->assertStatus(400)
            ->assertJson([
            'ids' => $ids, 'message' => 'Some ids are already in use.',

        ]);
        foreach ($data as $attribute_id => $attribute_type) {
            $this->assertDatabaseMissing('dynamic_attributes',
                ['attribute_id' => $attribute_id, 'attribute_type' => $attribute_type]);
        }
    }

    public function testUploadUnregisteredType()
    {
        $data = [
            'dynamic_attributes_test' => 'attribute.core.non_existant',
        ];
        $this->actingAs($this->user, 'api')->post('/admin/api/dynamic-attributes/upload', $data)
            ->assertStatus(400)
            ->assertJson([
            'types' => ['attribute.core.non_existant'], 'message' => 'Some types are not registered.'
        ]);
        $this->assertDatabaseMissing('dynamic_attributes',
            ['attribute_id' => 'dynamic_attributes_test', 'attribute_type' => 'attribute.core.non_existant']);
    }
}
