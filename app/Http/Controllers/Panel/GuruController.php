<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GuruController extends Controller
{
    public const STATUS = ['active' => 'Aktif', 'pending' => 'Menunggu verifikasi', 'inactive' => 'Nonaktif'];

    private const PESAN = [
        'name.required'      => 'Nama wajib diisi.',
        'email.required'     => 'Email wajib diisi.',
        'email.email'        => 'Format email tidak valid.',
        'email.unique'       => 'Email sudah dipakai.',
        'status.in'          => 'Status tidak valid.',
        'password.required'  => 'Password wajib diisi.',
        'password.min'       => 'Password minimal 6 karakter.',
        'password.confirmed' => 'Konfirmasi password tidak sama.',
    ];

    public function index(Request $request)
    {
        $q      = trim((string) $request->query('q'));
        $status = array_key_exists($request->query('status'), self::STATUS) ? $request->query('status') : null;

        $guru = User::where('role', 'guru')
            ->when($q !== '', function ($query) use ($q) {
                $like = '%' . mb_strtolower($q) . '%';
                $query->where(function ($w) use ($like) {
                    $w->whereRaw('LOWER(name) LIKE ?', [$like])
                      ->orWhereRaw('LOWER(email) LIKE ?', [$like]);
                });
            })
            ->when($status, fn ($query, $v) => $query->where('status', $v))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.guru.index', ['guru' => $guru, 'q' => $q, 'status' => $status, 'daftarStatus' => self::STATUS]);
    }

    public function create()
    {
        return view('admin.guru.form', ['guru' => new User(['status' => 'active']), 'daftarStatus' => self::STATUS]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'status'   => ['required', Rule::in(array_keys(self::STATUS))],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], self::PESAN);

        // Password di-hash otomatis oleh cast 'hashed' pada model User
        $guru = User::create($data + ['role' => 'guru']);

        AuditLog::record($request->user(), 'create_guru', 'users', $guru->id, $request->ip());

        return redirect()->route('admin.guru.index')->with('success', 'Akun guru berhasil ditambahkan.');
    }

    public function edit(User $guru)
    {
        abort_unless($guru->role === 'guru', 404);

        return view('admin.guru.form', ['guru' => $guru, 'daftarStatus' => self::STATUS]);
    }

    public function update(Request $request, User $guru)
    {
        abort_unless($guru->role === 'guru', 404);

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($guru->id)],
            'status'   => ['required', Rule::in(array_keys(self::STATUS))],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ], self::PESAN);

        if (empty($data['password'])) {
            unset($data['password']); // kosong = password tidak diubah
        }

        $guru->update($data);

        // Akun dinonaktifkan -> cabut semua token login aplikasinya
        if ($guru->status !== 'active') {
            $guru->tokens()->delete();
        }

        AuditLog::record($request->user(), 'update_guru', 'users', $guru->id, $request->ip());

        return redirect()->route('admin.guru.index')->with('success', 'Akun guru berhasil diperbarui.');
    }

    public function destroy(Request $request, User $guru)
    {
        abort_unless($guru->role === 'guru', 404);

        $id = $guru->id;
        $guru->tokens()->delete();
        $guru->delete();

        AuditLog::record($request->user(), 'delete_guru', 'users', $id, $request->ip());

        return redirect()->route('admin.guru.index')->with('success', 'Akun guru dihapus.');
    }
}
