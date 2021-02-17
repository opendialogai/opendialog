<?php

use App\Model;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use OpenDialogAi\AttributeEngine\Facades\AttributeResolver;
use OpenDialogAi\AttributeEngine\DynamicAttribute;

/** @var Factory $factory */
$factory->define(DynamicAttribute::class, function (Faker $faker) {
    return [
        'attribute_id' => $faker->unique()->regexify(AttributeResolver::getValidIdPattern()),
        'attribute_type' => $faker->randomElement([
            'attribute.core.int',
            'attribute.core.array',
            'attribute.core.boolean',
            'attribute.core.string',
            'attribute.core.timestamp'
        ])
    ];
});
