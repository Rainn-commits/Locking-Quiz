<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GuruAccessTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guru_receives_200_from_ping_endpoint(): void
    {
        $guru = User::factory()->guru()->create();

        Sanctum::actingAs($guru);

        $this->getJson('/api/guru/ping')
            ->assertOk()
            ->assertJsonPath('message', 'Halo, Guru!');
    }

    public function test_siswa_receives_403_from_ping_endpoint(): void
    {
        $siswa = User::factory()->create();

        Sanctum::actingAs($siswa);

        $this->getJson('/api/guru/ping')->assertForbidden();
    }

    public function test_guest_receives_401_from_ping_endpoint(): void
    {
        $this->getJson('/api/guru/ping')->assertUnauthorized();
    }
}
