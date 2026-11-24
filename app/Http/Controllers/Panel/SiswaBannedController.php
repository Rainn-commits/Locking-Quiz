<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class SiswaBannedController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));

        $banned = User::query()
            ->from('users as u')
            ->leftJoin('siswa as s', 's.user_id', '=', 'u.id')
            ->leftJoin('kelas as k', 'k.id', '=', 's.kelas_id')
            ->where('u.role', 'siswa')
            ->where('u.is_banned', true)
            ->when($q !== '', function ($query) use ($q) {
                $like = '%' . mb_strtolower($q) . '%';
                $query->where(function ($w) use ($like) {
                    $w->whereRaw('LOWER(u.name) LIKE ?', [$like])
                      ->orWhereRaw('LOWER(u.email) LIKE ?', [$like])
                      ->orWhereRaw('LOWER(s.nama_lengkap) LIKE ?', [$like])
                      ->orWhere('s.nis', 'like', $like);
                });
            })
            ->orderByDesc('u.banned_at')
            ->select('u.*', 's.nama_lengkap', 's.nis', 'k.nama as kelas')
            ->paginate(15)
            ->withQueryString();

        return view('admin.banned.index', compact('banned', 'q'));
    }

    public function unlock(Request $request, User $siswa)
    {
        abort_unless($siswa->role === 'siswa' && $siswa->is_banned, 404);

        $siswa->update([
            'is_banned'          => false,
            'banned_at'          => null,
            'jumlah_pelanggaran' => 0,
        ]);

        AuditLog::record($request->user(), 'unlock_siswa', 'users', $siswa->id, $request->ip());

        return back()->with('success', "Siswa {$siswa->name} berhasil di-unlock.");
    }

    public function unlockAll(Request $request)
    {
        $siswa = User::where('role', 'siswa')->where('is_banned', true)->get();

        foreach ($siswa as $s) {
            $s->update([
                'is_banned'          => false,
                'banned_at'          => null,
                'jumlah_pelanggaran' => 0,
            ]);
            AuditLog::record($request->user(), 'unlock_siswa', 'users', $s->id, $request->ip());
        }

        return back()->with('success', "{$siswa->count()} siswa berhasil di-unlock.");
    }
}
