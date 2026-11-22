<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class GuruController extends Controller
{
    public function index()
    {
        return User::where('role', 'guru')->latest()->paginate(15);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|max:150|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        $guru = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => 'guru',
            'status'   => 'active',
        ]);

        AuditLog::record($request->user(), 'create_guru', 'users', $guru->id, $request->ip());

        return response()->json($guru, 201);
    }

    public function update(Request $request, User $guru)
    {
        abort_unless($guru->role === 'guru', 404);

        $data = $request->validate([
            'name'     => 'sometimes|string|max:100',
            'email'    => ['sometimes', 'email', 'max:150', Rule::unique('users', 'email')->ignore($guru->id)],
            'password' => 'sometimes|string|min:6',
            'status'   => 'sometimes|in:pending,active,inactive',
        ]);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $guru->update($data);

        AuditLog::record($request->user(), 'update_guru', 'users', $guru->id, $request->ip());

        return $guru;
    }

    public function verify(Request $request, User $guru)
    {
        abort_unless($guru->role === 'guru', 404);

        if ($guru->status === 'active') {
            return response()->json(['message' => 'Akun guru sudah aktif.']);
        }

        $guru->update(['status' => 'active']);

        AuditLog::record($request->user(), 'verify_guru', 'users', $guru->id, $request->ip());

        return response()->json(['message' => 'Akun guru berhasil diverifikasi.', 'guru' => $guru]);
    }

    public function destroy(Request $request, User $guru)
    {
        abort_unless($guru->role === 'guru', 404);

        $id = $guru->id;
        $guru->delete();

        AuditLog::record($request->user(), 'delete_guru', 'users', $id, $request->ip());

        return response()->json(['message' => 'Akun guru dihapus.']);
    }
}
