<?php

use Illuminate\Support\Str;
use OpenDialogAi\ConversationBuilder\Conversation;
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

$factory->define(Conversation::class, function (Faker $faker) {
    $intent = Str::random(10);
    $name = Str::random(10);

    return [
        'name' => $name,
        'model' => "conversation:
  id: $name
  scenes:
    opening_scene:
      intents:
        - u: 
            i: intent.core.{$intent}
        - b: 
            i: intent.core.{$intent}Response
            completes: true",
    ];
});
