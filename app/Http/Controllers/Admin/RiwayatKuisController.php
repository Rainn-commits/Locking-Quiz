<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use Illuminate\Http\Request;

class RiwayatKuisController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'status'     => 'sometimes|in:draft,published',
            'creator_id' => 'sometimes|integer',
            'subject'    => 'sometimes|string|max:100',
            'q'          => 'sometimes|string|max:100',
        ]);

        return Quiz::query()
            ->with('creator:id,name,email')
            ->withCount('questions')
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->when($request->creator_id, fn ($q, $v) => $q->where('creator_id', $v))
            ->when($request->subject, fn ($q, $v) => $q->whereRaw('LOWER(subject) = ?', [mb_strtolower($v)]))
            ->when($request->q, fn ($q, $v) => $q->whereRaw('LOWER(title) LIKE ?', ['%' . mb_strtolower($v) . '%']))
            ->latest()
            ->paginate(20);
    }

    public function show(Quiz $quiz)
    {
        return $quiz->load([
            'creator:id,name,email',
            'questions' => fn ($q) => $q->orderBy('sort_order')->orderBy('id'),
            'questions.options' => fn ($q) => $q->orderBy('sort_order')->orderBy('id'),
        ])->loadCount('questions');
    }
}
