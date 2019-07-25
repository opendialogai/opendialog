<?php

use Illuminate\Support\Str;
use OpenDialogAi\ConversationLog\Message;
use Faker\Generator as Faker;

$factory->define(Message::class, function (Faker $faker) {
    return [
        'user_id' => $faker->name,
        'author' => $faker->name,
        'message' => $faker->text,
        'type' => 'text',
        'message_id' => Str::random(36),
    ];
});
