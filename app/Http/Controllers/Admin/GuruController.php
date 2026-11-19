<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class GuruController extends Controller
{
    public function index()
    {
        return User::where('role', 'guru')->latest()->paginate(15);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'username'     => 'required|string|max:50|unique:users,username',
            'password'     => 'required|string|min:6',
        ]);

        $guru = User::create([
            ...$data,
            'password' => Hash::make($data['password']),
            'role'     => 'guru',
        ]);

        return response()->json($guru, 201);
    }

    public function update(Request $request, User $guru)
    {
        abort_unless($guru->role === 'guru', 404);

        $data = $request->validate([
            'name' => 'sometimes|string|max:100',
            'username'     => 'sometimes|string|max:50|unique:users,username,' . $guru->id,
            'password'     => 'sometimes|string|min:6',
        ]);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $guru->update($data);
        return $guru;
    }

    public function destroy(User $guru)
    {
        abort_unless($guru->role === 'guru', 404);
        $guru->delete();
        return response()->json(['message' => 'Akun guru dihapus.']);
    }
}
