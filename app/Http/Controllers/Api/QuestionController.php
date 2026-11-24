<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuestionRequest;
use App\Http\Requests\UpdateQuestionRequest;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class QuestionController extends Controller
{
    public function index(Quiz $quiz): JsonResponse
    {
        Gate::authorize('view', $quiz);

        return response()->json([
            'questions' => $quiz->questions()->with('options')->orderBy('sort_order')->get(),
        ]);
    }

    public function store(StoreQuestionRequest $request, Quiz $quiz): JsonResponse
    {
        $data = $request->validated();

        $question = $quiz->questions()->create([
            'prompt' => $data['prompt'],
            'image_path' => $request->hasFile('image')
                ? $request->file('image')->store('questions', 'public')
                : null,
            'sort_order' => $quiz->questions()->count(),
        ]);

        $this->syncOptions($question, $data['options']);

        return response()->json([
            'message' => 'Soal berhasil dibuat',
            'question' => $question->load('options'),
        ], 201);
    }

    public function show(Quiz $quiz, Question $question): JsonResponse
    {
        Gate::authorize('view', $quiz);
        abort_unless($question->quiz_id === $quiz->id, 404);

        return response()->json(['question' => $question->load('options')]);
    }

    public function update(UpdateQuestionRequest $request, Quiz $quiz, Question $question): JsonResponse
    {
        abort_unless($question->quiz_id === $quiz->id, 404);

        $data = $request->validated();

        if (array_key_exists('prompt', $data)) {
            $question->prompt = $data['prompt'];
        }

        if ($request->hasFile('image')) {
            if ($question->image_path) {
                Storage::disk('public')->delete($question->image_path);
            }

            $question->image_path = $request->file('image')->store('questions', 'public');
        }

        $question->save();

        if (array_key_exists('options', $data)) {
            $question->options()->delete();
            $this->syncOptions($question, $data['options']);
        }

        return response()->json([
            'message' => 'Soal berhasil diperbarui',
            'question' => $question->load('options'),
        ]);
    }

    public function destroy(Quiz $quiz, Question $question): JsonResponse
    {
        Gate::authorize('delete', $quiz);
        abort_unless($question->quiz_id === $quiz->id, 404);

        $question->delete();

        return response()->json(['message' => 'Soal berhasil dihapus']);
    }

    /**
     * @param  array<int, array{text: string, is_correct: mixed}>  $options
     */
    private function syncOptions(Question $question, array $options): void
    {
        foreach ($options as $index => $option) {
            $question->options()->create([
                'option_text' => $option['text'],
                'is_correct' => filter_var($option['is_correct'], FILTER_VALIDATE_BOOLEAN),
                'sort_order' => $index,
            ]);
        }
    }
}
