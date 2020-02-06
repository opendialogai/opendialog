<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class GlobalContextsTest extends TestCase
{
    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        Artisan::call('webchat:settings');

        $this->user = factory(User::class)->create();
    }

    public function testItShouldAcceptZeroAndBeValid()
    {
        $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/global-context/', ['name' => 'ZeroValue', 'value' => 0])
            ->assertStatus(201);
    }

    public function testItShouldFailValidation()
    {
        $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/global-context/', ['name' => 'NoValue'])
            ->assertStatus(400)
            ->assertExactJson(['field' => 'name', 'message' => 'Global context value field is required.']);
    }
}
