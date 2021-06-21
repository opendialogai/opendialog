<?php

namespace App\Observers;

use App\User;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Str;
use Propaganistas\LaravelPhone\PhoneNumber;
use Srmklive\Authy\Facades\Authy;

class UserObserver
{
    public function creating(User $user)
    {
        $user->api_token = Str::random(60);
    }
}
