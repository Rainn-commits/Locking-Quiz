<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $stats = [
            'guru'          => User::where('role', 'guru')->count(),
            'siswa'         => DB::table('siswa')->count(),
            'biodata_lengkap' => DB::table('siswa')->where('biodata_lengkap', true)->count(),
            'banned'        => User::where('role', 'siswa')->where('is_banned', true)->count(),
        ];

        // Sebaran siswa per jurusan (hanya yang sudah memilih jurusan)
        $perJurusan = DB::table('jurusan as j')
            ->leftJoin('siswa as s', 's.jurusan_id', '=', 'j.id')
            ->groupBy('j.id', 'j.nama')
            ->orderBy('j.id')
            ->select('j.nama', DB::raw('COUNT(s.id) as total'))
            ->get();

        $maxJurusan = max(1, (int) $perJurusan->max('total'));

        $bannedTerbaru = User::where('role', 'siswa')
            ->where('is_banned', true)
            ->orderByDesc('banned_at')
            ->limit(5)
            ->get();

        $siswaTerbaru = DB::table('siswa as s')
            ->leftJoin('jurusan as j', 'j.id', '=', 's.jurusan_id')
            ->leftJoin('kelas as k', 'k.id', '=', 's.kelas_id')
            ->orderByDesc('s.created_at')
            ->limit(5)
            ->get(['s.id', 's.nama_lengkap', 's.nis', 'j.nama as jurusan', 'k.nama as kelas', 's.langkah', 's.created_at']);

        return view('admin.dashboard', compact('stats', 'perJurusan', 'maxJurusan', 'bannedTerbaru', 'siswaTerbaru'));
    }
}
