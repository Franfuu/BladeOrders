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
            'client_id'     => Client::factory(),
            'numero_pedido' => 'PED-' . $this->faker->unique()->numberBetween(1000, 9999),
            'fecha'         => $this->faker->dateTimeBetween('-1 year', 'now'),
            'estado'        => $this->faker->randomElement(['pendiente', 'enviado', 'entregado', 'cancelado']),
            'total'         => $this->faker->randomFloat(2, 10, 500)
        ];
    }
}
