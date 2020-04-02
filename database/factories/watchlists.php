<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\WatchList;
use Faker\Generator as Faker;

$factory->define(WatchList::class, static function (Faker $faker) {
    return [
        'uuid' =>$faker->uuid,
        'nid' => $faker->numberBetween(1,999999),
    ];

});
