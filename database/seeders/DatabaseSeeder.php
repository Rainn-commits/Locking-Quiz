<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call(BiodataSeeder::class);

        User::firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User', 'password' => 'password'],
        );

        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name'     => 'Administrator',
                'password' => 'ganti-password-ini',
                'role'     => 'admin',
            ],
        );
    }
}
