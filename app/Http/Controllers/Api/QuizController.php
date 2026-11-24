<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuizRequest;
use App\Http\Requests\UpdateQuizRequest;
use App\Models\Quiz;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class QuizController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Quiz::class);

        return response()->json([
            'quizzes' => $request->user()->quizzes()->latest()->get(),
        ]);
    }

    public function store(StoreQuizRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['subject'] = Subject::canonicalName($data['subject']);

        $quiz = $request->user()->quizzes()->create($data);

        return response()->json([
            'message' => 'Quiz berhasil dibuat',
            'quiz' => $quiz,
        ], 201);
    }

    public function show(Quiz $quiz): JsonResponse
    {
        Gate::authorize('view', $quiz);

        return response()->json(['quiz' => $quiz]);
    }

    public function update(UpdateQuizRequest $request, Quiz $quiz): JsonResponse
    {
        $data = $request->validated();

        if (array_key_exists('subject', $data)) {
            $data['subject'] = Subject::canonicalName($data['subject']);
        }

        $quiz->update($data);

        return response()->json([
            'message' => 'Quiz berhasil diperbarui',
            'quiz' => $quiz,
        ]);
    }

    public function publish(Quiz $quiz): JsonResponse
    {
        Gate::authorize('update', $quiz);

        $questions = $quiz->questions()->with('options')->get();

        if ($questions->isEmpty()) {
            return response()->json(['message' => 'Quiz harus punya minimal satu soal.'], 422);
        }

        foreach ($questions as $question) {
            if ($question->options->filter(fn ($option): bool => $option->is_correct)->count() !== 1) {
                return response()->json(['message' => 'Setiap soal harus punya tepat satu jawaban benar.'], 422);
            }
        }

        $quiz->update(['status' => 'published']);

        return response()->json([
            'message' => 'Quiz berhasil dipublish',
            'quiz' => $quiz,
        ]);
    }

    public function destroy(Quiz $quiz): JsonResponse
    {
        Gate::authorize('delete', $quiz);

        $quiz->delete();

        return response()->json(['message' => 'Quiz berhasil dihapus']);
    }
}
