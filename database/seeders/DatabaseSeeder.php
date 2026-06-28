<?php

namespace Database\Seeders;

use App\Models\User;
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
        // User::factory(10)->create();
        User::firstOrCreate(
            ['email' => 'abdelrhmanyosrymokhtar@gmail.com'],
            [
                'name' => 'Admin',
                'password' => 'test1234',
            ]
        );

        \App\Models\Employee::create([
            'name' => 'John Doe',
            'phone' => '0123456789',
            'commission_rate' => 50,
        ]);
        \App\Models\Employee::create([
            'name' => 'Jane Smith',
            'phone' => '0987654321',
            'commission_rate' => 45,
        ]);

        $this->call([
            ClientSeeder::class,
            ClientModelSeeder::class,
        ]);
    }
}
