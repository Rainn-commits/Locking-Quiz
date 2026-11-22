<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\UnlockToken;
use Illuminate\Http\Request;

class UnlockTokenController extends Controller
{
    public function redeem(Request $request)
    {
        $data = $request->validate([
            'token' => 'required|string|max:32',
        ]);

        $siswa = $request->user();

        if (! $siswa->is_banned) {
            return response()->json(['message' => 'Akun tidak berstatus BANNED.']);
        }

        $token = UnlockToken::where('student_id', $siswa->id)
            ->where('token', strtoupper(trim($data['token'])))
            ->first();

        if (! $token || ! $token->isValid()) {
            return response()->json(['message' => 'Token salah, kedaluwarsa, atau sudah digunakan.'], 422);
        }

        $token->consume();
        $siswa->unban();

        AuditLog::record($siswa, 'redeem_unlock_token', 'unlock_tokens', $token->id, $request->ip());

        return response()->json(['message' => 'Akses dibuka. Silakan lanjutkan kuis.']);
    }
}
