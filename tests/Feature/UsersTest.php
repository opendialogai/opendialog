<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;

class UsersTest extends TestCase
{
    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();

        for ($i = 0; $i < 52; $i++) {
            factory(User::class)->create();
        }
    }

    public function testUsersViewEndpoint()
    {
        $user = User::first();

        $this->get('/admin/api/user/' . $user->id)
            ->assertStatus(302);

        $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/user/' . $user->id)
            ->assertStatus(200)
            ->assertJsonFragment(
                [
                    'name' => $user->name,
                    'email' => $user->email,
                ]
            );
    }

    public function testUsersViewAllEndpoint()
    {
        $users = User::all();

        $this->get('/admin/api/user')
            ->assertStatus(302);

        $response = $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/user?page=1')
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    $users[0]->toArray(),
                    $users[1]->toArray(),
                    $users[2]->toArray(),
                ],
            ])
            ->getData();

        $this->assertEquals(count($response->data), 50);

        $response = $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/user?page=2')
            ->assertStatus(200)
            ->getData();

        $this->assertEquals(count($response->data), 3);
    }

    public function testUsersUpdateEndpoint()
    {
        $user = User::latest()->first();

        $this->actingAs($this->user, 'api')
            ->json('PATCH', '/admin/api/user/' . $user->id, [
                'name' => 'updated name',
            ])
            ->assertStatus(200);

        $updatedUser = User::latest()->first();

        $this->assertEquals($updatedUser->name, 'updated name');
    }

    public function testUsersStoreEndpoint()
    {
        $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/user', [
                'name' => 'test',
                'email' => 'test@test.com',
                'phone_number' => '+41 (0)22 76 761 11',
            ])
            ->assertStatus(201)
            ->assertJsonFragment(
                [
                    'name' => 'test',
                    'email' => 'test@test.com',
                    'phone_country_code' => 41,
                    'phone_number' => '022 767 61 11',
                ]
            );
    }

    public function testUsersStoreWithoutPhoneNumberEndpoint()
    {
        $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/user', [
                'name' => 'test',
                'email' => 'test@test.com',
            ])
            ->assertStatus(201)
            ->assertJsonFragment(
                [
                    'name' => 'test',
                    'email' => 'test@test.com',
                ]
            );
    }

    public function testUsersDestroyEndpoint()
    {
        $user = User::first();

        $this->actingAs($this->user, 'api')
            ->json('DELETE', '/admin/api/user/' . $user->id)
            ->assertStatus(200);

        $this->assertEquals(User::find($user->id), null);
    }

    public function testUsersInvalidStoreEndpoint()
    {
        $response = $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/user', [
                'email' => 'test@test.com',
                'phone_number' => '+41 (0)22 76 761 11',
            ])
            ->assertStatus(400);

        $this->assertEquals($response->content(), '{"field":"name","message":"User name field is required."}');

        $response = $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/user', [
                'name' => 'test',
                'phone_number' => '+41 (0)22 76 761 11',
            ])
            ->assertStatus(400);

        $this->assertEquals($response->content(), '{"field":"email","message":"User email field is required."}');

        $response = $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/user', [
                'name' => 'test',
                'email' => 'test@test.com',
                'phone_number' => '1',
            ])
            ->assertStatus(400);

        $this->assertEquals($response->content(), '{"field":"phone_number","message":"Enter a valid phone number with prefix."}');
    }

    public function testUsersInvalidUpdateEndpoint()
    {
        $user = User::first();

        $response = $this->actingAs($this->user, 'api')
            ->json('PATCH', '/admin/api/user/' . $user->id, [
                'name' => '',
            ])
            ->assertStatus(400);

        $this->assertEquals($response->content(), '{"field":"name","message":"User name field is required."}');
    }
}
