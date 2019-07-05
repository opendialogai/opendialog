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

        factory(User::class)->create();
        factory(User::class)->create();
    }

    public function testUsersViewEndpoint()
    {
        $user = User::first();

        $this->get('/admin/api/user/' . $user->id)
            ->assertStatus(302);

        $this->actingAs($this->user)
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

        $response = $this->actingAs($this->user)
            ->json('GET', '/admin/api/user')
            ->assertStatus(200)
            ->assertJsonCount(count($users))
            ->assertJson([
                $users[0]->toArray(),
                $users[1]->toArray(),
                $users[2]->toArray(),
            ]);
    }

    public function testUsersUpdateEndpoint()
    {
        $user = User::latest()->first();

        $this->actingAs($this->user)
            ->json('PATCH', '/admin/api/user/' . $user->id, [
                'name' => 'updated name',
            ])
            ->assertStatus(200);

        $updatedUser = User::latest()->first();

        $this->assertEquals($updatedUser->name, 'updated name');
    }

    public function testUsersStoreEndpoint()
    {
        $this->actingAs($this->user)
            ->json('POST', '/admin/api/user', [
                'name' => 'test',
                'email' => 'test@test.com',
                'password' => 'test',
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

        $this->actingAs($this->user)
            ->json('DELETE', '/admin/api/user/' . $user->id)
            ->assertStatus(405);
    }
}
