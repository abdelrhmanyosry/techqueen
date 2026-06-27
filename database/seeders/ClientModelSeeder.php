<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ClientModel;
use Illuminate\Database\Seeder;

class ClientModelSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            'in_progress',
            'completed',
            'delayed',
            'canceled',
        ];

        Client::all()->each(function ($client) use ($statuses) {

            for ($i = 1; $i <= 5; $i++) {

                ClientModel::create([
                    'client_id' => $client->id,

                    'piece_name' => "Piece {$i}",

                    'notes' => 'Sample notes',

                    'modification' => 'Small adjustment',

                    'receiving_date' => now()
                        ->subDays(rand(1, 20)),

                    'delivery_date' => now()
                        ->addDays(rand(-5, 15)),

                    'deposit' => rand(100, 1000),

                    'price' => rand(500, 5000),

                    'status' => $statuses[array_rand($statuses)],

                    'completed_at' => rand(0, 1)
                        ? now()
                        : null,
                ]);
            }
        });
    }
}