<?php

namespace Tests\Unit;

use App\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateUserCommandTest extends TestCase
{
    public function testCreateUserCommand()
    {
        $this->artisan('user:create')
            ->expectsQuestion('First Name', 'First')
            ->expectsQuestion('Last Name', 'Last')
            ->expectsQuestion('Email', 'email@example.com')
            ->expectsQuestion('Password', 'test')
            ->expectsQuestion('Repeat password', 'test2')
            ->expectsOutput("Passwords do not match!")
            ->expectsQuestion('Password', 'test')
            ->expectsQuestion('Repeat password', 'test')
            ->expectsOutput('User created with id 1')
            ->assertExitCode(0);

        /** @var User $user */
        $user = User::all()->first();

        $this->assertEquals("email@example.com", $user->email);
        $this->assertEquals("First Last", $user->name);
        $this->assertTrue(Hash::check("test", $user->password));

        // test the --checkUser flag
        $this->artisan('user:create', ['--userCheck' => true])
            ->assertExitCode(0);
    }

    public function testCreateUserCommandArguments()
    {
        $this->artisan('user:create', [
            'first' => 'First',
            'last' => 'Last',
            'email' => 'email@example.com',
            'password' => 'test'
        ])
            ->expectsOutput('User created with id 1')
            ->assertExitCode(0);

        /** @var User $user */
        $user = User::all()->first();

        $this->assertEquals("email@example.com", $user->email);
        $this->assertEquals("First Last", $user->name);
        $this->assertTrue(Hash::check("test", $user->password));

        // test the --checkUser flag
        $this->artisan('user:create', ['--userCheck' => true])
            ->assertExitCode(0);
    }
}
