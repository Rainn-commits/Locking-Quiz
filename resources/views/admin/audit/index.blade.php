@extends('admin.layouts.app')

@section('title', 'Audit Log')
@section('heading', 'Audit Log')
@section('subheading', 'Catatan aktivitas penting: login, kelola akun, unlock siswa, reset password, dan perubahan data kelas.')

@section('content')
@php
    $warnaAksi = function (string $a) {
        if (str_starts_with($a, 'delete_')) return 'badge-merah';
        if (str_contains($a, 'unlock') || $a === 'reset_password') return 'badge-kuning';
        if (str_starts_with($a, 'create_') || $a === 'verify_guru') return 'badge-hijau';
        return 'badge-ungu';
    };
    $labelRole = ['admin' => 'Admin', 'guru' => 'Guru', 'siswa' => 'Siswa'];
    $adaFilter = $q !== '' || $aksi !== '' || $dari || $sampai;
@endphp

<div class="card">
    <form method="GET" class="toolbar">
        <input type="search" name="q" value="{{ $q }}" class="input grow" placeholder="Cari nama atau email pelaku…">
        <select name="aksi" class="input" style="width:auto">
            <option value="">Semua aksi</option>
            @foreach ($daftarAksi as $val => $label)
                <option value="{{ $val }}" @selected($aksi === $val)>{{ $label }}</option>
            @endforeach
        </select>
        <input type="date" name="dari" value="{{ $dari }}" class="input" style="width:auto" title="Dari tanggal">
        <input type="date" name="sampai" value="{{ $sampai }}" class="input" style="width:auto" title="Sampai tanggal">
        <button class="btn btn-navy">@include('admin.partials.icon', ['name' => 'search']) Cari</button>
        @if ($adaFilter)
            <a href="{{ route('admin.audit.index') }}" class="btn">Reset</a>
        @endif
    </form>

    @if ($log->isEmpty())
        <div class="empty">
            @include('admin.partials.icon', ['name' => 'inbox'])
            {{ $adaFilter ? 'Tidak ada aktivitas yang cocok dengan filter.' : 'Belum ada aktivitas yang tercatat.' }}
        </div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                <tr><th>Waktu</th><th>Pelaku</th><th>Aksi</th><th>Data</th><th>IP</th></tr>
                </thead>
                <tbody>
                @foreach ($log as $l)
                    <tr>
                        <td>
                            {{ $l->created_at?->translatedFormat('d M Y, H:i') }}
                            <span class="sub">{{ $l->created_at?->diffForHumans() }}</span>
                        </td>
                        <td>
                            @if ($l->user)
                                <b>{{ $l->user->name }}</b>
                                <span class="sub">{{ $labelRole[$l->user->role] ?? $l->user->role }} · {{ $l->user->email }}</span>
                            @else
                                <span class="muted">Akun sudah dihapus</span>
                            @endif
                        </td>
                        <td><span class="badge {{ $warnaAksi($l->action) }}">{{ $labelAksi[$l->action] ?? $l->action }}</span></td>
                        <td class="muted">{{ $l->entity }}{{ $l->entity_id ? ' #' . $l->entity_id : '' }}</td>
                        <td class="muted">{{ $l->ip_address ?? '—' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{ $log->links('admin.partials.pagination') }}
</div>
@endsection
