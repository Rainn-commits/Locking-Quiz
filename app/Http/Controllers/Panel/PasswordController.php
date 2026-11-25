<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class PasswordController extends Controller
{
    public const ROLE = ['guru' => 'Guru', 'siswa' => 'Siswa'];

    public function index(Request $request)
    {
        $q    = trim((string) $request->query('q'));
        $role = array_key_exists($request->query('role'), self::ROLE) ? $request->query('role') : null;

        $pengguna = User::whereIn('role', User::ROLE_BISA_RESET)
            ->when($role, fn ($query, $v) => $query->where('role', $v))
            ->when($q !== '', function ($query) use ($q) {
                $like = '%' . mb_strtolower($q) . '%';
                $query->where(function ($w) use ($like) {
                    $w->whereRaw('LOWER(name) LIKE ?', [$like])
                      ->orWhereRaw('LOWER(email) LIKE ?', [$like]);
                });
            })
            ->orderBy('role')->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.password.index', [
            'pengguna'   => $pengguna,
            'q'          => $q,
            'role'       => $role,
            'daftarRole' => self::ROLE,
        ]);
    }

    public function edit(User $user)
    {
        abort_unless($user->bisaDiresetPassword(), 404);

        return view('admin.password.form', ['pengguna' => $user, 'daftarRole' => self::ROLE]);
    }

    public function update(ResetPasswordRequest $request, User $user)
    {
        abort_unless($user->bisaDiresetPassword(), 404);

        $user->resetPassword($request->validated('password'));

        AuditLog::record($request->user(), 'reset_password', 'users', $user->id, $request->ip());

        return redirect()->route('admin.password.index')
            ->with('success', "Password {$user->name} berhasil direset. Pengguna perlu login ulang dengan password baru.");
    }
}
