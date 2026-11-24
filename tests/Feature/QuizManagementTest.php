<?php

namespace Tests\Feature;

use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class QuizManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_valid_payload_creates_quiz_and_returns_201(): void
    {
        $guru = User::factory()->guru()->create();

        Sanctum::actingAs($guru);

        $response = $this->postJson('/api/quizzes', [
            'title' => 'Quiz Matematika',
            'subject' => 'Matematika',
            'description' => 'Ujian tengah semester',
            'start_at' => now()->addDay()->toIso8601String(),
            'end_at' => now()->addDays(2)->toIso8601String(),
            'duration_minutes' => 60,
        ]);

        $response->assertCreated()
            ->assertJsonPath('message', 'Quiz berhasil dibuat')
            ->assertJsonPath('quiz.title', 'Quiz Matematika');

        $this->assertDatabaseHas('quizzes', [
            'creator_id' => $guru->id,
            'title' => 'Quiz Matematika',
            'status' => 'draft',
            'duration_minutes' => 60,
        ]);
    }

    public function test_siswa_receives_403_when_creating_quiz(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/quizzes', [
            'title' => 'Quiz',
            'subject' => 'IPA',
        ])->assertForbidden();
    }

    public function test_other_guru_receives_403_when_updating_quiz(): void
    {
        $pemilik = User::factory()->guru()->create();
        $quiz = Quiz::factory()->for($pemilik, 'creator')->create();
        $guruLain = User::factory()->guru()->create();

        Sanctum::actingAs($guruLain);

        $this->putJson("/api/quizzes/{$quiz->id}", ['title' => 'Dicuri'])
            ->assertForbidden();
    }

    public function test_owner_updates_quiz_and_receives_200(): void
    {
        $guru = User::factory()->guru()->create();
        $quiz = Quiz::factory()->for($guru, 'creator')->create();

        Sanctum::actingAs($guru);

        $this->putJson("/api/quizzes/{$quiz->id}", ['title' => 'Judul Baru'])
            ->assertOk()
            ->assertJsonPath('message', 'Quiz berhasil diperbarui')
            ->assertJsonPath('quiz.title', 'Judul Baru');

        $this->assertDatabaseHas('quizzes', [
            'id' => $quiz->id,
            'title' => 'Judul Baru',
        ]);
    }

    public function test_end_at_before_start_at_is_rejected(): void
    {
        $guru = User::factory()->guru()->create();

        Sanctum::actingAs($guru);

        $this->postJson('/api/quizzes', [
            'title' => 'Quiz',
            'subject' => 'IPA',
            'start_at' => now()->addDay()->toIso8601String(),
            'end_at' => now()->toIso8601String(),
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('end_at')
            ->assertJsonPath('errors.end_at.0', 'Waktu selesai harus setelah waktu mulai.');
    }

    public function test_index_returns_only_own_quizzes(): void
    {
        $guru = User::factory()->guru()->create();
        $quizSendiri = Quiz::factory()->for($guru, 'creator')->create();
        Quiz::factory()->create();

        Sanctum::actingAs($guru);

        $this->getJson('/api/quizzes')
            ->assertOk()
            ->assertJsonCount(1, 'quizzes')
            ->assertJsonPath('quizzes.0.id', $quizSendiri->id);
    }

    public function test_policy_allows_owner_and_forbids_other_users(): void
    {
        $pemilik = User::factory()->guru()->create();
        $quiz = Quiz::factory()->for($pemilik, 'creator')->create();
        $guruLain = User::factory()->guru()->create();
        $siswa = User::factory()->create();

        $this->assertTrue(Gate::forUser($pemilik)->allows('viewAny', Quiz::class));
        $this->assertTrue(Gate::forUser($pemilik)->allows('update', $quiz));
        $this->assertFalse(Gate::forUser($guruLain)->allows('update', $quiz));
        $this->assertFalse(Gate::forUser($siswa)->allows('create', Quiz::class));
    }
}
