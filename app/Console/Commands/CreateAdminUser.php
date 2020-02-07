<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateAdminUser extends Command
{
    protected $signature = 'admin_user:create';

    protected $description = 'Creates the first admin user if one does not exit';

    public function handle()
    {
        if (User::where('email', 'admin@example.com')->first()) {
            $this->info('Not recreating user');
            exit;
        }

        /** @var User $user */
        $user = new User();

        $user->name = "admin";
        $user->email = "admin@example.com";
        $user->password = Hash::make("opendialog");
        $user->api_token = Str::random();

        $user->save();
    }
}
