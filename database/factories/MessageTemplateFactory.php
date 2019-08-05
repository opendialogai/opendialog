<?php

use Illuminate\Support\Str;
use OpenDialogAi\ResponseEngine\MessageTemplate;
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

$factory->define(MessageTemplate::class, function (Faker $faker) {
    return [
        'name' => $faker->unique()->name,
        'conditions' => '',
        'message_markup' => '<message><text-message>Test</text-message></message>',
    ];
});
