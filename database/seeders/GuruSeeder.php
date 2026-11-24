<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GuruSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $guru = User::firstOrCreate(
            ['email' => 'guru@example.com'],
            ['name' => 'Guru Satu', 'password' => 'password']
        );
        $guru->forceFill(['role' => 'guru'])->save();
    }
}
