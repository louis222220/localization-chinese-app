<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\UploadDocElement;
use Faker\Generator as Faker;

$factory->define(UploadDocElement::class, function (Faker $faker) {
    return [
        'origin_element_id' => 5,
        'origin_value' => "Kotlin provides a set of built-in types that represent numbers.",
        'now_value' => "Kotlin 提供了...",
        'font_size' => rand(1, 3)
    ];
});
