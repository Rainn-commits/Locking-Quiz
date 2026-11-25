<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public const AKSI = [
        'login'               => 'Login',
        'logout'              => 'Logout',
        'create_guru'         => 'Tambah guru',
        'update_guru'         => 'Ubah guru',
        'verify_guru'         => 'Verifikasi guru',
        'delete_guru'         => 'Hapus guru',
        'issue_unlock_token'  => 'Terbitkan unlock token',
        'redeem_unlock_token' => 'Pakai unlock token',
        'override_unlock'     => 'Override unlock',
        'unlock_siswa'        => 'Unlock siswa (panel)',
        'reset_password'      => 'Reset password',
        'create_jurusan'      => 'Tambah jurusan',
        'update_jurusan'      => 'Ubah jurusan',
        'delete_jurusan'      => 'Hapus jurusan',
        'create_kelas'        => 'Tambah kelas',
        'update_kelas'        => 'Ubah kelas',
        'delete_kelas'        => 'Hapus kelas',
        'import_siswa'        => 'Impor siswa',
    ];

    public function index(Request $request)
    {
        $q    = trim((string) $request->query('q'));
        $aksi = (string) $request->query('aksi');
        $dari = $request->date('dari');
        $sampai = $request->date('sampai');

        $log = AuditLog::with('user:id,name,email,role')
            ->when($aksi !== '', fn ($query) => $query->where('action', $aksi))
            ->when($dari, fn ($query, $d) => $query->where('created_at', '>=', $d->startOfDay()))
            ->when($sampai, fn ($query, $d) => $query->where('created_at', '<=', $d->endOfDay()))
            ->when($q !== '', function ($query) use ($q) {
                $like = '%' . mb_strtolower($q) . '%';
                $query->whereHas('user', function ($w) use ($like) {
                    $w->whereRaw('LOWER(name) LIKE ?', [$like])
                      ->orWhereRaw('LOWER(email) LIKE ?', [$like]);
                });
            })
            ->latest('created_at')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $daftarAksi = AuditLog::query()->distinct()->orderBy('action')->pluck('action')
            ->mapWithKeys(fn ($a) => [$a => self::AKSI[$a] ?? $a]);

        return view('admin.audit.index', [
            'log'        => $log,
            'q'          => $q,
            'aksi'       => $aksi,
            'dari'       => $dari?->format('Y-m-d'),
            'sampai'     => $sampai?->format('Y-m-d'),
            'daftarAksi' => $daftarAksi,
            'labelAksi'  => self::AKSI,
        ]);
    }
}
