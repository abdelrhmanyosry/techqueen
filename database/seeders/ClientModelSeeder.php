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
            'canceled',
            'on_hold',
            'finished_unpaid',
            'paid_unfinished',
            'finished_paid',
        ];

        $employees = \App\Models\Employee::all();

        Client::all()->each(function ($client) use ($statuses, $employees) {

            for ($i = 1; $i <= 5; $i++) {

                ClientModel::create([
                    'client_id' => $client->id,
                    'employee_id' => rand(0, 2) ? $employees->random()->id : null,

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