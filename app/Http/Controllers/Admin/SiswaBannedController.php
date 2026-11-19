<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class SiswaBannedController extends Controller
{
    public function index()
    {
        return User::where('role', 'siswa')
            ->where('is_banned', true)
            ->orderByDesc('banned_at')
            ->paginate(15);
    }

    public function unlock(User $siswa)
    {
        abort_unless($siswa->role === 'siswa' && $siswa->is_banned, 404);

        $siswa->update([
            'is_banned' => false,
            'banned_at' => null,
            'jumlah_pelanggaran' => 0,
        ]);

        return response()->json(['message' => 'Siswa berhasil di-unlock.']);
    }
}
