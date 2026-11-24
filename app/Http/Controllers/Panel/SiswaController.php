<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Data biodata siswa yang diisi dari aplikasi mobile
 * (nama -> NIS -> jurusan -> tingkat -> rombel).
 */
class SiswaController extends Controller
{
    public function index(Request $request)
    {
        $filter = [
            'q'          => trim((string) $request->query('q')),
            'jurusan_id' => $request->integer('jurusan_id') ?: null,
            'tingkat_id' => $request->integer('tingkat_id') ?: null,
            'kelas_id'   => $request->integer('kelas_id') ?: null,
            'status'     => in_array($request->query('status'), ['lengkap', 'belum'], true) ? $request->query('status') : null,
        ];

        $siswa = DB::table('siswa as s')
            ->leftJoin('jurusan as j', 'j.id', '=', 's.jurusan_id')
            ->leftJoin('tingkat_kelas as t', 't.id', '=', 's.tingkat_id')
            ->leftJoin('kelas as k', 'k.id', '=', 's.kelas_id')
            ->leftJoin('users as u', 'u.id', '=', 's.user_id')
            ->when($filter['q'] !== '', function ($query) use ($filter) {
                $like = '%' . mb_strtolower($filter['q']) . '%';
                $query->where(function ($w) use ($like) {
                    $w->whereRaw('LOWER(s.nama_lengkap) LIKE ?', [$like])
                      ->orWhere('s.nis', 'like', $like);
                });
            })
            ->when($filter['jurusan_id'], fn ($q, $v) => $q->where('s.jurusan_id', $v))
            ->when($filter['tingkat_id'], fn ($q, $v) => $q->where('s.tingkat_id', $v))
            ->when($filter['kelas_id'], fn ($q, $v) => $q->where('s.kelas_id', $v))
            ->when($filter['status'] === 'lengkap', fn ($q) => $q->where('s.biodata_lengkap', true))
            ->when($filter['status'] === 'belum', fn ($q) => $q->where('s.biodata_lengkap', false))
            ->orderByDesc('s.created_at')
            ->select([
                's.id', 's.nama_lengkap', 's.nis', 's.langkah', 's.biodata_lengkap', 's.selesai_at', 's.created_at',
                'j.nama as jurusan', 't.romawi as tingkat', 'k.nama as kelas',
                'u.email', 'u.is_banned',
            ])
            ->paginate(20)
            ->withQueryString();

        $jurusan = DB::table('jurusan')->orderBy('id')->get(['id', 'nama']);
        $tingkat = DB::table('tingkat_kelas')->orderBy('angka')->get(['id', 'romawi']);
        $kelas   = DB::table('kelas')
            ->when($filter['jurusan_id'], fn ($q, $v) => $q->where('jurusan_id', $v))
            ->when($filter['tingkat_id'], fn ($q, $v) => $q->where('tingkat_id', $v))
            ->orderBy('tingkat_id')->orderBy('jurusan_id')->orderBy('nomor')
            ->get(['id', 'nama']);

        return view('admin.siswa.index', compact('siswa', 'filter', 'jurusan', 'tingkat', 'kelas'));
    }

    public function destroy(int $id)
    {
        $deleted = DB::table('siswa')->where('id', $id)->delete();

        abort_if(! $deleted, 404);

        return back()->with('success', 'Data biodata siswa dihapus.');
    }
}
