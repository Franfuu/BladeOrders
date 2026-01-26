<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
   public function definition(): array
{
    return [
        'nombre' => fake()->name(),
        'email' => fake()->unique()->safeEmail(),
        'telefono' => fake()->phoneNumber(),
        'direccion' => fake()->address()
    ];
}
}
