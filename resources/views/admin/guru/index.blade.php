@extends('admin.layouts.app')

@section('title', 'Akun Guru')
@section('heading', 'Akun Guru')
@section('subheading', 'Kelola akun guru yang dapat membuat kelas dan kuis.')

@section('actions')
    <a href="{{ route('admin.guru.create') }}" class="btn btn-primary">@include('admin.partials.icon', ['name' => 'plus']) Tambah Guru</a>
@endsection

@section('content')
<div class="card">
    <form method="GET" class="toolbar">
        <input type="search" name="q" value="{{ $q }}" class="input grow" placeholder="Cari nama atau email…">
        <select name="status" class="input" style="width:auto">
            <option value="">Semua status</option>
            @foreach ($daftarStatus as $val => $label)
                <option value="{{ $val }}" @selected($status === $val)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="btn btn-navy">@include('admin.partials.icon', ['name' => 'search']) Cari</button>
        @if ($q !== '' || $status)
            <a href="{{ route('admin.guru.index') }}" class="btn">Reset</a>
        @endif
    </form>

    @if ($guru->isEmpty())
        <div class="empty">
            @include('admin.partials.icon', ['name' => 'inbox'])
            {{ ($q !== '' || $status) ? 'Tidak ada guru yang cocok dengan pencarian.' : 'Belum ada akun guru. Klik "Tambah Guru" untuk membuat.' }}
        </div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                <tr><th>#</th><th>Nama</th><th>Email</th><th>Status</th><th>Dibuat</th><th class="text-right">Aksi</th></tr>
                </thead>
                <tbody>
                @foreach ($guru as $i => $g)
                    <tr>
                        <td class="muted">{{ $guru->firstItem() + $i }}</td>
                        <td><b>{{ $g->name }}</b></td>
                        <td>{{ $g->email }}</td>
                        <td>
                            @php $warna = ['active' => 'badge-hijau', 'pending' => 'badge-kuning', 'inactive' => 'badge-merah'][$g->status] ?? 'badge-ungu'; @endphp
                            <span class="badge {{ $warna }}">{{ $daftarStatus[$g->status] ?? $g->status }}</span>
                        </td>
                        <td class="muted">{{ $g->created_at?->translatedFormat('d M Y') }}</td>
                        <td>
                            <div class="actions">
                                <a href="{{ route('admin.guru.edit', $g) }}" class="btn btn-sm">@include('admin.partials.icon', ['name' => 'edit']) Edit</a>
                                <form method="POST" action="{{ route('admin.guru.destroy', $g) }}" class="inline"
                                      onsubmit="return confirm('Hapus akun guru {{ addslashes($g->name) }}? Tindakan ini tidak bisa dibatalkan.')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm">@include('admin.partials.icon', ['name' => 'trash']) Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{ $guru->links('admin.partials.pagination') }}
</div>
@endsection
