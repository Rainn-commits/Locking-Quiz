<?php

namespace Tests\Feature;

use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SubjectManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guru_creates_subject_with_optional_image(): void
    {
        Storage::fake('public');
        Sanctum::actingAs(User::factory()->guru()->create());

        $response = $this->post('/api/subjects', [
            'name' => 'Matematika',
            'image' => UploadedFile::fake()->image('math.jpg'),
        ]);

        $response->assertCreated()
            ->assertJsonPath('message', 'Mata pelajaran berhasil dibuat')
            ->assertJsonPath('subject.name', 'Matematika');

        $subject = Subject::firstWhere('name', 'Matematika');
        Storage::disk('public')->assertExists($subject->image_path);
        $this->assertStringContainsString($subject->image_path, $response->json('subject.image_url'));
    }

    public function test_subject_name_is_unique_case_insensitively(): void
    {
        Subject::factory()->create(['name' => 'IPA']);
        Sanctum::actingAs(User::factory()->guru()->create());

        $this->postJson('/api/subjects', ['name' => 'ipa'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('name');
    }

    public function test_siswa_receives_403_on_subject_endpoints(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/subjects')->assertForbidden();
        $this->postJson('/api/subjects', ['name' => 'IPA'])->assertForbidden();
    }

    public function test_updating_subject_replaces_image_and_deleting_removes_it(): void
    {
        Storage::fake('public');
        Sanctum::actingAs(User::factory()->guru()->create());

        $subject = Subject::factory()->create([
            'image_path' => Storage::disk('public')->putFile('subjects', UploadedFile::fake()->image('old.jpg')),
        ]);
        $oldPath = $subject->image_path;

        $this->put("/api/subjects/{$subject->id}", [
            'image' => UploadedFile::fake()->image('new.jpg'),
        ])->assertOk();

        $subject->refresh();
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($subject->image_path);

        $newPath = $subject->image_path;

        $this->deleteJson("/api/subjects/{$subject->id}")->assertOk();

        Storage::disk('public')->assertMissing($newPath);
        $this->assertDatabaseMissing('subjects', ['id' => $subject->id]);
    }

    public function test_quiz_subject_is_canonicalized_and_exposes_subject_image(): void
    {
        Storage::fake('public');
        $subject = Subject::factory()->create([
            'name' => 'Matematika',
            'image_path' => Storage::disk('public')->putFile('subjects', UploadedFile::fake()->image('math.jpg')),
        ]);
        Sanctum::actingAs(User::factory()->guru()->create());

        $response = $this->postJson('/api/quizzes', [
            'title' => 'Quiz',
            'subject' => 'matematika',
        ]);

        $response->assertCreated()
            ->assertJsonPath('quiz.subject', 'Matematika')
            ->assertJsonPath('quiz.subject_image_url', Storage::url($subject->image_path));

        $this->assertDatabaseHas('quizzes', ['subject' => 'Matematika']);
    }
}
