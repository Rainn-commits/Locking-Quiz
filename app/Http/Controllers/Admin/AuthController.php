<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $data['email'])
            ->whereIn('role', ['admin', 'guru'])
            ->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Email atau password salah.'], 401);
        }

        if ($user->status !== 'active') {
            return response()->json(['message' => 'Akun belum diverifikasi atau tidak aktif.'], 403);
        }

        AuditLog::record($user, 'login', 'users', $user->id, $request->ip());

        return response()->json([
            'token' => $user->createToken('dashboard-token')->plainTextToken,
            'user'  => $user->only('id', 'name', 'email', 'role'),
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        AuditLog::record($user, 'logout', 'users', $user->id, $request->ip());
        $user->currentAccessToken()->delete();

        return response()->json(['message' => 'Berhasil logout.']);
    }
}
