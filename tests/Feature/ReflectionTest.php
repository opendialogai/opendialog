<?php


namespace Tests\Feature;


use App\User;
use Tests\TestCase;

class ReflectionTest extends TestCase
{
    public function testReflectionAllEndpoint()
    {
        $response = $this->actingAs(factory(User::class)->create())
            ->get('/reflection/all')
            ->assertStatus(200)
            ->json();

        $this->assertArrayHasKey('action_engine', $response);
        $this->assertArrayHasKey('attribute_engine', $response);
        $this->assertArrayHasKey('context_engine', $response);
        $this->assertArrayHasKey('interpreter_engine', $response);
        $this->assertArrayHasKey('operation_engine', $response);
        $this->assertArrayHasKey('response_engine', $response);
        $this->assertArrayHasKey('sensor_engine', $response);
    }
}
