<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubjectRequest;
use App\Http\Requests\UpdateSubjectRequest;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class SubjectController extends Controller
{
    public function index(): JsonResponse
    {
        $subjects = Subject::orderBy('name')->get()->map(function (Subject $subject): Subject {
            $subject->sum_quiz = $subject->quizCount();

            return $subject;
        });

        return response()->json([
            'subjects' => $subjects,
        ]);
    }

    public function store(StoreSubjectRequest $request): JsonResponse
    {
        $data = $request->validated();
        unset($data['image']);

        $data['image_path'] = $request->hasFile('image')
            ? $request->file('image')->store('subjects', 'public')
            : null;

        $subject = Subject::create($data);

        return response()->json([
            'message' => 'Mata pelajaran berhasil dibuat',
            'subject' => $subject,
        ], 201);
    }

    public function update(UpdateSubjectRequest $request, Subject $subject): JsonResponse
    {
        $data = $request->validated();

        if (array_key_exists('name', $data)) {
            $subject->name = $data['name'];
        }

        if ($request->hasFile('image')) {
            if ($subject->image_path) {
                Storage::disk('public')->delete($subject->image_path);
            }

            $subject->image_path = $request->file('image')->store('subjects', 'public');
        }

        $subject->save();

        return response()->json([
            'message' => 'Mata pelajaran berhasil diperbarui',
            'subject' => $subject,
        ]);
    }

    public function destroy(Subject $subject): JsonResponse
    {
        $subject->delete();

        return response()->json(['message' => 'Mata pelajaran berhasil dihapus']);
    }
}
