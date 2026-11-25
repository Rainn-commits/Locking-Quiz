<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\AuditLog;
use App\Models\User;

class UserPasswordController extends Controller
{
    public function update(ResetPasswordRequest $request, User $user)
    {
        if (! $user->bisaDiresetPassword()) {
            return response()->json([
                'message' => 'Hanya password akun guru atau siswa yang dapat direset.',
            ], 422);
        }

        $user->resetPassword($request->validated('password'));

        AuditLog::record($request->user(), 'reset_password', 'users', $user->id, $request->ip());

        return response()->json([
            'message' => "Password {$user->name} berhasil direset. Pengguna perlu login ulang.",
        ]);
    }
}
