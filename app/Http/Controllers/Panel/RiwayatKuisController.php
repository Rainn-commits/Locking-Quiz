<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Http\Request;

class RiwayatKuisController extends Controller
{
    public const STATUS = ['draft' => 'Draft', 'published' => 'Dipublikasikan'];

    public function index(Request $request)
    {
        $filter = [
            'q'          => trim((string) $request->query('q')),
            'status'     => array_key_exists($request->query('status'), self::STATUS) ? $request->query('status') : null,
            'creator_id' => $request->integer('creator_id') ?: null,
            'subject'    => trim((string) $request->query('subject')),
        ];

        $kuis = Quiz::query()
            ->with('creator:id,name')
            ->withCount('questions')
            ->when($filter['status'], fn ($q, $v) => $q->where('status', $v))
            ->when($filter['creator_id'], fn ($q, $v) => $q->where('creator_id', $v))
            ->when($filter['subject'] !== '', fn ($q) => $q->whereRaw('LOWER(subject) = ?', [mb_strtolower($filter['subject'])]))
            ->when($filter['q'] !== '', fn ($q) => $q->whereRaw('LOWER(title) LIKE ?', ['%' . mb_strtolower($filter['q']) . '%']))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.riwayat-kuis.index', [
            'kuis'        => $kuis,
            'filter'      => $filter,
            'daftarStatus' => self::STATUS,
            'daftarGuru'  => User::where('role', 'guru')->whereHas('quizzes')->orderBy('name')->get(['id', 'name']),
            'daftarMapel' => Quiz::query()->distinct()->orderBy('subject')->pluck('subject'),
        ]);
    }

    public function show(Quiz $quiz)
    {
        $quiz->load([
            'creator:id,name,email',
            'questions' => fn ($q) => $q->orderBy('sort_order')->orderBy('id'),
            'questions.options' => fn ($q) => $q->orderBy('sort_order')->orderBy('id'),
        ]);

        return view('admin.riwayat-kuis.show', [
            'kuis'         => $quiz,
            'daftarStatus' => self::STATUS,
        ]);
    }
}
