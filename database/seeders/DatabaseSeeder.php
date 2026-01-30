<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Order;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear 10 clientes de prueba
        $clients = Client::factory(10)->create();

        // Crear 2-5 orders para cada cliente
        $clients->each(function ($client) {
            Order::factory(rand(2, 5))->create([
                'client_id' => $client->id
            ]);
        });
    }
}
