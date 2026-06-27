<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $clients = [
            [
                'name' => 'Ahmed Ali',
                'field' => 'Dental Clinic',
                'phone' => '01012345678',
            ],

            [
                'name' => 'Mohamed Hassan',
                'field' => 'Engineering',
                'phone' => '01198765432',
            ],

            [
                'name' => 'Sara Mahmoud',
                'field' => 'Industrial Design',
                'phone' => '01255555555',
            ],
        ];

        foreach ($clients as $client) {
            Client::create($client);
        }
    }
}