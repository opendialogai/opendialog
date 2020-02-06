<?php

use Illuminate\Support\Str;
use OpenDialogAi\Core\RequestLog;
use Faker\Generator as Faker;

$factory->define(RequestLog::class, function (Faker $faker) {
    return [
        'url' => '/incoming/webchat',
        'query_params' => '[]',
        'method' => 'POST',
        'source_ip' => '192.168.10.1',
        'request_id' => Str::random(26),
        'raw_request' => '',
        'user_id' => $faker->name,
        'microtime' => DateTime::createFromFormat('U.u', microtime(true))->format('Y-m-d H:i:s.u'),
    ];
});
