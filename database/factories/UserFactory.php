<?php

use App\Models\Role;
use App\Models\User;
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

$factory->define(User::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'email_verified_at' => now(),
        'password' => bcrypt('secret'),
        'remember_token' => str_random(10),
        'role_id' => Role::ADMINISTRATOR,
    ];
});

$factory->state(User::class, 'administrator', [
    'role_id' => Role::ADMINISTRATOR,
]);

$factory->state(User::class, 'basic-user', [
    'role_id' => Role::BASIC,
]);
