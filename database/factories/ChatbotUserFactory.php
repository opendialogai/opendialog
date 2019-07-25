<?php

use Illuminate\Support\Str;
use OpenDialogAi\ConversationLog\ChatbotUser;
use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(ChatbotUser::class, function (Faker $faker) {
    return [
        'user_id' => Str::random(20),
        'first_name' => Str::random(10),
        'last_name' => Str::random(10),
        'email' => $faker->unique()->safeEmail,
    ];
});
