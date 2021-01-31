<?php

use App\Model;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use OpenDialogAi\ContextEngine\AttributeResolver\AttributeResolver;
use OpenDialogAi\Core\DynamicAttribute;

/** @var Factory $factory */
$factory->define(DynamicAttribute::class, function (Faker $faker) {
    return [
        'attribute_id' => $faker->unique()->regexify(AttributeResolver::$validIdPattern),
        'attribute_type' => $faker->regexify(AttributeResolver::$validTypePattern)
    ];
});
