@extends('admin.layouts.app')

@section('title', 'Jurusan')
@section('heading', 'Jurusan')
@section('subheading', 'Daftar jurusan yang bisa dipilih siswa saat mengisi biodata.')

@section('actions')
    <a href="{{ route('admin.jurusan.create') }}" class="btn btn-primary">@include('admin.partials.icon', ['name' => 'plus']) Tambah Jurusan</a>
@endsection

@section('content')
<div class="card">
    @if ($jurusan->isEmpty())
        <div class="empty">
            @include('admin.partials.icon', ['name' => 'inbox'])
            Belum ada jurusan. Klik "Tambah Jurusan" untuk membuat.
        </div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                <tr><th>#</th><th>Kode</th><th>Nama Jurusan</th><th>Kelas</th><th>Siswa</th><th class="text-right">Aksi</th></tr>
                </thead>
                <tbody>
                @foreach ($jurusan as $i => $j)
                    <tr>
                        <td class="muted">{{ $i + 1 }}</td>
                        <td><span class="badge badge-ungu">{{ $j->kode }}</span></td>
                        <td><b>{{ $j->nama }}</b></td>
                        <td>
                            <a href="{{ route('admin.kelas.index', ['jurusan_id' => $j->id]) }}">{{ $j->kelas_count }} kelas</a>
                        </td>
                        <td>{{ $j->siswa_count }} siswa</td>
                        <td>
                            <div class="actions">
                                <a href="{{ route('admin.jurusan.edit', $j) }}" class="btn btn-sm">@include('admin.partials.icon', ['name' => 'edit']) Edit</a>
                                <form method="POST" action="{{ route('admin.jurusan.destroy', $j) }}" class="inline"
                                      onsubmit="return confirm('Hapus jurusan {{ addslashes($j->nama) }}?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm" @disabled($j->kelas_count > 0 || $j->siswa_count > 0)
                                            @style(["opacity:.45;cursor:not-allowed" => $j->kelas_count > 0 || $j->siswa_count > 0])
                                            title="{{ ($j->kelas_count > 0 || $j->siswa_count > 0) ? 'Jurusan masih memiliki kelas atau siswa' : 'Hapus jurusan' }}">
                                        @include('admin.partials.icon', ['name' => 'trash']) Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
