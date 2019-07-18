<?php

use Illuminate\Support\Str;
use OpenDialogAi\ResponseEngine\OutgoingIntent;
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

$factory->define(OutgoingIntent::class, function (Faker $faker) {
    return [
        'name' => Str::random(20),
    ];
});
