<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuestionRequest;
use App\Http\Requests\UpdateQuestionRequest;
use App\Models\Question;
use App\Models\Quiz;
use App\Services\QuestionCsvParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

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
     * Impor soal dari file CSV (kolom: soal,a,b,c,d,e,jawaban).
     * Kalau ada satu baris saja yang salah, tidak ada soal yang disimpan.
     */
    public function import(Request $request, Quiz $quiz, QuestionCsvParser $parser): JsonResponse
    {
        Gate::authorize('update', $quiz);

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ], [
            'file.required' => 'File CSV wajib diunggah.',
            'file.mimes' => 'File harus berformat .csv.',
        ]);

        try {
            $result = $parser->parseFile($request->file('file')->getRealPath());
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        if ($result['errors'] !== []) {
            return response()->json([
                'message' => 'Ada baris yang tidak valid. Tidak ada soal yang disimpan.',
                'errors' => $result['errors'],
            ], 422);
        }

        if ($result['questions'] === []) {
            return response()->json(['message' => 'Tidak ada soal di file CSV.'], 422);
        }

        $created = DB::transaction(function () use ($quiz, $result) {
            $order = $quiz->questions()->count();

            return collect($result['questions'])->map(function (array $item) use ($quiz, &$order) {
                $question = $quiz->questions()->create([
                    'prompt' => $item['prompt'],
                    'sort_order' => $order++,
                ]);

                $this->syncOptions($question, $item['options']);

                return $question->load('options');
            });
        });

        return response()->json([
            'message' => $created->count().' soal berhasil diimpor',
            'questions' => $created,
        ], 201);
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
