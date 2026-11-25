@extends('admin.layouts.app')

@section('title', 'Reset Password')
@section('heading', 'Reset Password')
@section('subheading', 'Atur ulang password akun guru atau siswa yang lupa password.')

@section('content')
<div class="card">
    <form method="GET" class="toolbar">
        <input type="search" name="q" value="{{ $q }}" class="input grow" placeholder="Cari nama atau email…">
        <select name="role" class="input" style="width:auto">
            <option value="">Guru dan siswa</option>
            @foreach ($daftarRole as $val => $label)
                <option value="{{ $val }}" @selected($role === $val)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="btn btn-navy">@include('admin.partials.icon', ['name' => 'search']) Cari</button>
        @if ($q !== '' || $role)
            <a href="{{ route('admin.password.index') }}" class="btn">Reset</a>
        @endif
    </form>

    @if ($pengguna->isEmpty())
        <div class="empty">
            @include('admin.partials.icon', ['name' => 'inbox'])
            {{ ($q !== '' || $role) ? 'Tidak ada akun yang cocok dengan pencarian.' : 'Belum ada akun guru atau siswa.' }}
        </div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                <tr><th>#</th><th>Nama</th><th>Email</th><th>Role</th><th class="text-right">Aksi</th></tr>
                </thead>
                <tbody>
                @foreach ($pengguna as $i => $u)
                    <tr>
                        <td class="muted">{{ $pengguna->firstItem() + $i }}</td>
                        <td><b>{{ $u->name }}</b></td>
                        <td>{{ $u->email }}</td>
                        <td><span class="badge {{ $u->role === 'guru' ? 'badge-ungu' : 'badge-kuning' }}">{{ $daftarRole[$u->role] ?? $u->role }}</span></td>
                        <td>
                            <div class="actions">
                                <a href="{{ route('admin.password.edit', $u) }}" class="btn btn-sm">@include('admin.partials.icon', ['name' => 'key']) Reset Password</a>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{ $pengguna->links('admin.partials.pagination') }}
</div>
@endsection
