<?php

namespace Tests\Feature;

use App\User;
use OpenDialogAi\Core\DynamicAttribute;
use Tests\TestCase;

/**
 * Class DynamicAttributesTest
 * @package Tests\Feature
 * @group SpecificationTests
 */
class DynamicAttributesTest extends TestCase
{
    const invalidIds = ['testDynamicAttribute', 'test_Dynamic.Attribute', 'test_dynamic_attribute_1', 'test_Dynamic.Attribute_1', 'testdynamicattribute1'];
    const invalidTypes = ['attributeCoreString', 'attribute.string', 'core.string', 'string', 'wrong.core.string'];
    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();

        DynamicAttribute::truncate();

    }

    public function testViewEndpoint()
    {
        $dynamicAttribute = factory(DynamicAttribute::class)->create();

        $this->get('/admin/api/dynamic-attribute/' . $dynamicAttribute->id)
            ->assertStatus(302);

        $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/dynamic-attribute/' . $dynamicAttribute->id)
            ->assertStatus(200)
            ->assertJsonFragment(
                [
                    'attribute_id' => $dynamicAttribute->attribute_id,
                    'attribute_type' => $dynamicAttribute->attribute_type
                ]
            );
    }

    public function testViewAllEndpoint()
    {
        for ($i = 0; $i < 52; $i++) {
            factory(DynamicAttribute::class)->create();
        }
        $dynamicAttributes = DynamicAttribute::all();

        $this->get('/admin/api/dynamic-attribute')
            ->assertStatus(302);

        $response = $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/dynamic-attribute?page=1')
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    $dynamicAttributes[0]->toArray(),
                    $dynamicAttributes[1]->toArray(),
                    $dynamicAttributes[2]->toArray(),
                ],
            ])
            ->getData();

        $this->assertEquals(count($response->data), 50);

        $response = $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/dynamic-attribute?page=2')
            ->assertStatus(200)
            ->getData();

        $this->assertEquals(count($response->data), 2);
    }

    public function testUpdateEndpoint()
    {
        /* A complete update */
        $a = factory(DynamicAttribute::class)->create();
        $goodData = [
            'attribute_id' => 'updated_id_a',
            'attribute_type' => 'attribute.core.string'
        ];
        $this->actingAs($this->user, 'api')
            ->json('PATCH', '/admin/api/dynamic-attribute/' . $a->id, $goodData)
            ->assertStatus(204);
        $updatedDynamicAttribute = DynamicAttribute::find($a->id);

        $this->assertEquals($updatedDynamicAttribute->attribute_id, $goodData['attribute_id']);
        $this->assertEquals($updatedDynamicAttribute->attribute_type, $goodData['attribute_type']);


        /* A partial update (id only) */
        $b = factory(DynamicAttribute::class)->create();
        $idOnly = [
            'attribute_id' => 'updated_id_b',
        ];
        $this->actingAs($this->user, 'api')
            ->json('PATCH', '/admin/api/dynamic-attribute/' . $b->id, $idOnly)
            ->assertStatus(204);
        $updatedDynamicAttribute = DynamicAttribute::find($b->id);

        $this->assertEquals($updatedDynamicAttribute->attribute_id, $idOnly['attribute_id']);
        $this->assertEquals($updatedDynamicAttribute->attribute_type, $b->attribute_type);


        /* A partial update (type only) */
        $c = factory(DynamicAttribute::class)->create();
        $typeOnly = [
            'attribute_type' => 'attribute.core.int'
        ];
        $this->actingAs($this->user, 'api')
            ->json('PATCH', '/admin/api/dynamic-attribute/' . $c->id, $typeOnly)
            ->assertStatus(204);
        $updatedDynamicAttribute = DynamicAttribute::find($c->id);

        $this->assertEquals($updatedDynamicAttribute->attribute_id, $c->attribute_id);
        $this->assertEquals($updatedDynamicAttribute->attribute_type, $typeOnly['attribute_type']);


        /* Bad update (Duplicate attribute_id) */
        $d = factory(DynamicAttribute::class)->create();
        $e = factory(DynamicAttribute::class)->create();
        $duplicateId = [
            'attribute_id' => $e->attribute_id
        ];
        $this->actingAs($this->user, 'api')
            ->json('PATCH', '/admin/api/dynamic-attribute/' . $d->id, $duplicateId)
            ->assertStatus(400);

        /* Bad Updates (Invalid attribute_ids) */
        $f = factory(DynamicAttribute::class)->create();
        foreach (self::invalidIds as $invalidId) {
            $data = [
                'attribute_id' => $invalidId
            ];
            $this->actingAs($this->user, 'api')
                ->json('PATCH', '/admin/api/dynamic-attribute/' . $f->id, $data)
                ->assertStatus(400);
        }

        /* Bad Updates (Invalid attribute_types) */
        $g = factory(DynamicAttribute::class)->create();
        foreach (self::invalidTypes as $invalidType) {
            $data = [
                'attribute_type' => $invalidType
            ];
            $this->actingAs($this->user, 'api')
                ->json('PATCH', '/admin/api/dynamic-attribute/' . $g->id, $data)
                ->assertStatus(400);
        }


    }

    public function testStoreEndpoint()
    {
        /* Successful */
        $goodData = [
            'attribute_id' => 'test_dynamic_attribute_a',
            'attribute_type' => 'attribute.core.string'
        ];
        $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/dynamic-attribute', $goodData)
            ->assertStatus(201)
            ->assertJsonFragment($goodData);

        /* Missing attribute_id */
        $missingId = [
            'attribute_type' => 'attribute.core.string',
        ];
        $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/dynamic-attribute', $missingId)
            ->assertStatus(400)
            ->assertJsonFragment(
                [
                    'field' => 'attribute_id',
                    'message' => 'attribute_id field is required.',
                ]
            );

        /* Missing attribute_type */
        $missingType = [
            'attribute_id' => 'test_dynamic_attribute_b'
        ];
        $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/dynamic-attribute', $missingType)
            ->assertStatus(400)
            ->assertJsonFragment(
                [
                    'field' => 'attribute_type',
                    'message' => 'attribute_type field is required.',
                ]
            );


        /* Invalid attribute ids (wrong format) */
        $invalidIdFormats = array_map(fn($id) => ['attribute_id' => $id, 'attribute_type' => 'attribute.core.string'], self::invalidIds);
        foreach ($invalidIdFormats as $invalidIdFormat) {
            $this->actingAs($this->user, 'api')
                ->json('POST', '/admin/api/dynamic-attribute', $invalidIdFormat)
                ->assertStatus(400)
                ->assertJsonFragment(
                    [
                        'field' => 'attribute_id',
                        'message' => 'attribute_id field must follow snake_case format.',
                    ]
                );
        }

        /* Invalid attribute types (wrong format) */
        $invalidTypeFormats = array_map(fn($type) => ['attribute_id' => 'attribute_id', 'attribute_type' => $type], self::invalidIds);
        foreach ($invalidTypeFormats as $invalidTypeFormat) {
            $this->actingAs($this->user, 'api')
                ->json('POST', '/admin/api/dynamic-attribute', $invalidTypeFormat)
                ->assertStatus(400)
                ->assertJsonFragment(
                    [
                        'field' => 'attribute_type',
                        'message' => "attribute_type field must follow the format: 'attribute.<component_name>.<type>'",
                    ]
                );
        }
    }

    public function testDestroyEndpoint()
    {
        $dynamicAttribute = factory(DynamicAttribute::class)->create();

        $this->actingAs($this->user, 'api')
            ->json('DELETE', '/admin/api/dynamic-attribute/' . $dynamicAttribute->id)
            ->assertStatus(204);

        $this->assertEquals(DynamicAttribute::find($dynamicAttribute->id), null);
    }


    public function testDownloadEndpoint()
    {
        for ($i = 0; $i < 52; $i++) {
            factory(DynamicAttribute::class)->create();
        }
        $data = DynamicAttribute::all();

        $expected = [];
        foreach ($data as $datum) {
            $expected[$datum->attribute_id] = $datum->attribute_type;
        }

        $this->actingAs($this->user, 'api')->get('/admin/api/dynamic-attributes/download')->assertStatus(200)->assertJson($expected);

    }

    public function testUploadEndpoint()
    {
        /* No user for auth. Should redirect */
        $goodData = [
            'dynamic_attributes_test_a' => 'attribute.core.string',
            'dynamic_attributes_test_b' => 'attribute.core.int',
            'dynamic_attributes_test_c' => 'attribute.core.err'
        ];
        $this->post('/admin/api/dynamic-attributes/upload', $goodData)->assertStatus(302);

        /* A good upload */
        $this->actingAs($this->user, 'api')->post('/admin/api/dynamic-attributes/upload', $goodData)->assertStatus(201)->assertJson($goodData);
        foreach ($goodData as $id => $type) {
            $this->assertDatabaseHas('dynamic_attributes', ['attribute_id' => $id, 'attribute_type' => $type]);
        }

        /* Uploading duplicate data should fail */
        $this->actingAs($this->user, 'api')->post('/admin/api/dynamic-attributes/upload', $goodData)->assertStatus(400);

        /* Uploading nothing should fail */
        $noData = [];
        $this->actingAs($this->user, 'api')->post('/admin/api/dynamic-attributes/upload', $noData)->assertStatus(400);

        /* Uploading with bad Id should fail */
        foreach (self::invalidIds as $id) {
            $data = [
                $id => 'attribute.core.int'
            ];
            $this->actingAs($this->user, 'api')->post('/admin/api/dynamic-attributes/upload', $data)->assertStatus(400);
        }

        /* Uploading with bad type data should fail */
        foreach (self::invalidTypes as $type) {
            $data = [
                'dynamic_attributes_test' => $type
            ];
            $this->actingAs($this->user, 'api')->post('/admin/api/dynamic-attributes/upload', $data)->assertStatus(400);
        }

        $duplicateConfigID = [
            //TODO: Upload ID overwriting config ID
        ];

        $nonExistantType = [
            //TODO: Upload non-existant type
        ];
    }
}
