<?php

namespace Tests\Feature;

use App\User;
use OpenDialogAi\ResponseEngine\OutgoingIntent;
use Tests\TestCase;

/**
 * Class OutgoingIntentsTest
 * @package Tests\Feature
 * @group SpecificationTests
 */
class OutgoingIntentsTest extends TestCase
{
    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();

        for ($i = 0; $i < 52; $i++) {
            factory(OutgoingIntent::class)->create();
        }
    }

    public function testOutgoingIntentsViewEndpoint()
    {
        $outgoingIntent = OutgoingIntent::first();

        $this->get('/admin/api/outgoing-intent/' . $outgoingIntent->id)
            ->assertStatus(302);

        $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/outgoing-intent/' . $outgoingIntent->id)
            ->assertStatus(200)
            ->assertJsonFragment(
                [
                    'name' => $outgoingIntent->name,
                ]
            );
    }

    public function testOutgoingIntentsViewAllEndpoint()
    {
        $outgoingIntents = OutgoingIntent::all();

        $this->get('/admin/api/outgoing-intent')
            ->assertStatus(302);

        $response = $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/outgoing-intent?page=1')
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    $outgoingIntents[0]->toArray(),
                    $outgoingIntents[1]->toArray(),
                    $outgoingIntents[2]->toArray(),
                ],
            ])
            ->getData();

        $this->assertEquals(count($response->data), 50);

        $response = $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/outgoing-intent?page=2')
            ->assertStatus(200)
            ->getData();

        $this->assertEquals(count($response->data), 2);
    }

    public function testOutgoingIntentsUpdateEndpoint()
    {
        $outgoingIntent = OutgoingIntent::latest()->first();

        $this->actingAs($this->user, 'api')
            ->json('PATCH', '/admin/api/outgoing-intent/' . $outgoingIntent->id, [
                'name' => 'updated name',
            ])
            ->assertStatus(200);

        $updatedOutgoingIntent = OutgoingIntent::latest()->first();

        $this->assertEquals($updatedOutgoingIntent->name, 'updated name');
    }

    public function testOutgoingIntentsStoreEndpoint()
    {
        $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/outgoing-intent', [
                'name' => 'test',
            ])
            ->assertStatus(201)
            ->assertJsonFragment(
                [
                    'name' => 'test',
                ]
            );
    }

    public function testOutgoingIntentsDestroyEndpoint()
    {
        $outgoingIntent = OutgoingIntent::first();

        $this->actingAs($this->user, 'api')
            ->json('DELETE', '/admin/api/outgoing-intent/' . $outgoingIntent->id)
            ->assertStatus(200);

        $this->assertEquals(OutgoingIntent::find($outgoingIntent->id), null);
    }
}
