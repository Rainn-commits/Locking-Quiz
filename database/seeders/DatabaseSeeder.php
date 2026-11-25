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
        $this->call(GuruSeeder::class);
        $this->call(SubjectSeeder::class);

        User::firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User', 'password' => 'password'],
        );

        // updateOrCreate: password admin ikut diperbarui walau akunnya sudah ada di database
        User::updateOrCreate(
            ['email' => 'admin@lockingquiz.test'],
            ['name' => 'Administrator', 'password' => '123456', 'role' => 'admin', 'status' => 'active'],
        );

        User::firstOrCreate(
            ['email' => 'guru@lockingquiz.test'],
            ['name' => 'Guru Contoh', 'password' => 'password', 'role' => 'guru', 'status' => 'active'],
        );

        User::firstOrCreate(
            ['email' => 'siswa@lockingquiz.test'],
            [
                'name'               => 'Siswa Contoh',
                'password'           => 'password',
                'role'               => 'siswa',
                'status'             => 'active',
                'is_banned'          => true,
                'banned_at'          => now(),
                'jumlah_pelanggaran' => 3,
            ],
        );
    }
}
