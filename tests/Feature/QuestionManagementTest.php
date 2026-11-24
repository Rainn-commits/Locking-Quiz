<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class QuestionManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'prompt' => 'Berapa 2 + 2?',
            'options' => [
                ['text' => '3', 'is_correct' => false],
                ['text' => '4', 'is_correct' => true],
            ],
        ], $overrides);
    }

    public function test_guru_creates_question_with_image_and_options(): void
    {
        Storage::fake('public');
        $guru = User::factory()->guru()->create();
        $quiz = Quiz::factory()->for($guru, 'creator')->create();
        Sanctum::actingAs($guru);

        $response = $this->post("/api/quizzes/{$quiz->id}/questions", $this->payload([
            'image' => UploadedFile::fake()->image('soal.png'),
        ]));

        $response->assertCreated()
            ->assertJsonPath('message', 'Soal berhasil dibuat')
            ->assertJsonCount(2, 'question.options');

        $question = Question::firstWhere('prompt', 'Berapa 2 + 2?');
        Storage::disk('public')->assertExists($question->image_path);
        $this->assertSame(1, $question->options()->where('is_correct', true)->count());
        $this->assertStringContainsString($question->image_path, $response->json('question.image_url'));
    }

    public function test_question_without_image_is_allowed(): void
    {
        $guru = User::factory()->guru()->create();
        $quiz = Quiz::factory()->for($guru, 'creator')->create();
        Sanctum::actingAs($guru);

        $this->post("/api/quizzes/{$quiz->id}/questions", $this->payload())
            ->assertCreated()
            ->assertJsonPath('question.image_url', null);

        $this->assertDatabaseHas('questions', [
            'quiz_id' => $quiz->id,
            'image_path' => null,
        ]);
    }

    public function test_no_correct_option_is_rejected(): void
    {
        $guru = User::factory()->guru()->create();
        $quiz = Quiz::factory()->for($guru, 'creator')->create();
        Sanctum::actingAs($guru);

        $this->postJson("/api/quizzes/{$quiz->id}/questions", $this->payload([
            'options' => [
                ['text' => '3', 'is_correct' => false],
                ['text' => '4', 'is_correct' => false],
            ],
        ]))->assertUnprocessable()
            ->assertJsonValidationErrors('options')
            ->assertJsonPath('errors.options.0', 'Harus ada tepat satu jawaban benar.');
    }

    public function test_more_than_one_correct_option_is_rejected(): void
    {
        $guru = User::factory()->guru()->create();
        $quiz = Quiz::factory()->for($guru, 'creator')->create();
        Sanctum::actingAs($guru);

        $this->postJson("/api/quizzes/{$quiz->id}/questions", $this->payload([
            'options' => [
                ['text' => '3', 'is_correct' => true],
                ['text' => '4', 'is_correct' => true],
            ],
        ]))->assertUnprocessable()->assertJsonValidationErrors('options');
    }

    public function test_other_guru_receives_403_when_adding_question(): void
    {
        $quiz = Quiz::factory()->for(User::factory()->guru()->create(), 'creator')->create();
        Sanctum::actingAs(User::factory()->guru()->create());

        $this->postJson("/api/quizzes/{$quiz->id}/questions", $this->payload())
            ->assertForbidden();
    }

    public function test_siswa_receives_403_when_reading_questions(): void
    {
        $quiz = Quiz::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $this->getJson("/api/quizzes/{$quiz->id}/questions")->assertForbidden();
    }

    public function test_update_without_new_image_keeps_existing_image(): void
    {
        Storage::fake('public');
        $guru = User::factory()->guru()->create();
        $quiz = Quiz::factory()->for($guru, 'creator')->create();
        $question = Question::factory()->for($quiz)->create([
            'image_path' => Storage::disk('public')->putFile('questions', UploadedFile::fake()->image('soal.jpg')),
        ]);
        $originalPath = $question->image_path;
        Sanctum::actingAs($guru);

        $this->put("/api/quizzes/{$quiz->id}/questions/{$question->id}", [
            'prompt' => 'Soal revisi',
        ])->assertOk();

        $question->refresh();
        $this->assertSame('Soal revisi', $question->prompt);
        $this->assertSame($originalPath, $question->image_path);
        Storage::disk('public')->assertExists($originalPath);
    }

    public function test_update_with_new_image_replaces_old_image(): void
    {
        Storage::fake('public');
        $guru = User::factory()->guru()->create();
        $quiz = Quiz::factory()->for($guru, 'creator')->create();
        $question = Question::factory()->for($quiz)->create([
            'image_path' => Storage::disk('public')->putFile('questions', UploadedFile::fake()->image('old.jpg')),
        ]);
        $oldPath = $question->image_path;
        Sanctum::actingAs($guru);

        $this->put("/api/quizzes/{$quiz->id}/questions/{$question->id}", [
            'image' => UploadedFile::fake()->image('new.jpg'),
        ])->assertOk();

        $question->refresh();
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($question->image_path);
    }

    public function test_deleting_question_removes_image(): void
    {
        Storage::fake('public');
        $guru = User::factory()->guru()->create();
        $quiz = Quiz::factory()->for($guru, 'creator')->create();
        $question = Question::factory()->for($quiz)->create([
            'image_path' => Storage::disk('public')->putFile('questions', UploadedFile::fake()->image('soal.jpg')),
        ]);
        $path = $question->image_path;
        Sanctum::actingAs($guru);

        $this->deleteJson("/api/quizzes/{$quiz->id}/questions/{$question->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Soal berhasil dihapus');

        Storage::disk('public')->assertMissing($path);
        $this->assertDatabaseMissing('questions', ['id' => $question->id]);
    }

    public function test_publish_without_question_is_rejected(): void
    {
        $guru = User::factory()->guru()->create();
        $quiz = Quiz::factory()->for($guru, 'creator')->create();
        Sanctum::actingAs($guru);

        $this->postJson("/api/quizzes/{$quiz->id}/publish")
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Quiz harus punya minimal satu soal.');

        $this->assertSame('draft', $quiz->fresh()->status);
    }

    public function test_publish_with_valid_question_succeeds(): void
    {
        $guru = User::factory()->guru()->create();
        $quiz = Quiz::factory()->for($guru, 'creator')->create();
        $question = Question::factory()->for($quiz)->create();
        QuestionOption::factory()->for($question)->correct()->create();
        QuestionOption::factory()->for($question)->create();
        Sanctum::actingAs($guru);

        $this->postJson("/api/quizzes/{$quiz->id}/publish")
            ->assertOk()
            ->assertJsonPath('message', 'Quiz berhasil dipublish')
            ->assertJsonPath('quiz.status', 'published');

        $this->assertSame('published', $quiz->fresh()->status);
    }
}
