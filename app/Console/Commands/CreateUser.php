<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateUser extends Command
{
    protected $signature = 'user:create {--userCheck} {first?} {last?} {email?} {password?}';

    protected $description = 'Create an admin user in the system';

    public function handle()
    {
        if ($this->option('userCheck') && User::count() >0) {
            $this->warn('Not creating user, at least 1 already exists');
            return 0;
        }

        $this->info('Creating user - please enter details');

        $user = new User();

        $first = $this->argument('first') ?? $this->ask('First Name');
        $last = $this->argument('last') ?? $this->ask('Last Name');

        $user->name = "$first $last";

        $user->email = $this->argument('email') ?? $this->ask('Email');

        $password = $this->argument('password') ?? $this->createPassword();

        $user->password = Hash::make($password);

        $user->save();

        $this->info("User created with id {$user->id}");
    }

    private function createPassword(): string
    {
        $password1 = $this->secret('Password');
        $password2 = $this->secret('Repeat password');

        if ($password1 != $password2) {
            $this->warn("Passwords do not match!");
            return $this->createPassword();
        }

        return $password1;
    }
}
