<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'numero_pedido' => 'PED-' . fake()->unique()->numberBetween(1000, 9999),
            'fecha' => fake()->dateTimeBetween('-1 year', 'now'),
            'estado' => fake()->randomElement(['pendiente', 'enviado', 'entregado', 'cancelado']),
            'total' => fake()->randomFloat(2, 10, 500)
        ];
    }
}
