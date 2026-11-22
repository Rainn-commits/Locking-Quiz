<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\UnlockToken;
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

    public function issueToken(Request $request, User $siswa)
    {
        abort_unless($siswa->role === 'siswa', 404);

        if (! $siswa->is_banned) {
            return response()->json(['message' => 'Akun siswa tidak berstatus BANNED.'], 422);
        }

        $token = UnlockToken::issueFor($siswa, $request->user());

        AuditLog::record($request->user(), 'issue_unlock_token', 'unlock_tokens', $token->id, $request->ip());

        return response()->json([
            'message'    => 'Token unlock berhasil diterbitkan.',
            'token'      => $token->token,
            'expires_at' => $token->expires_at,
        ], 201);
    }

    public function override(Request $request, User $siswa)
    {
        abort_unless($siswa->role === 'siswa', 404);

        if (! $siswa->is_banned) {
            return response()->json(['message' => 'Akun siswa sudah aktif.']);
        }

        $siswa->unban();
        UnlockToken::revokeActiveFor($siswa);

        AuditLog::record($request->user(), 'override_unlock', 'users', $siswa->id, $request->ip());

        return response()->json(['message' => 'Siswa berhasil di-unlock (override).']);
    }
}
