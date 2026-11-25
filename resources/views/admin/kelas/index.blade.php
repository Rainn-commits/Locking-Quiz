@extends('admin.layouts.app')

@section('title', 'Kelas')
@section('heading', 'Kelas (Rombel)')
@section('subheading', 'Rombongan belajar per jurusan dan tingkat. Nama kelas dibuat otomatis, cth. "X Perbankan 1".')

@section('actions')
    <a href="{{ route('admin.kelas.create', array_filter($filter)) }}" class="btn btn-primary">@include('admin.partials.icon', ['name' => 'plus']) Tambah Kelas</a>
@endsection

@section('content')
<div class="card">
    <form method="GET" class="toolbar">
        <select name="jurusan_id" class="input" style="width:auto" onchange="this.form.submit()">
            <option value="">Semua jurusan</option>
            @foreach ($daftarJurusan as $j)
                <option value="{{ $j->id }}" @selected($filter['jurusan_id'] == $j->id)>{{ $j->nama }}</option>
            @endforeach
        </select>
        <select name="tingkat_id" class="input" style="width:auto" onchange="this.form.submit()">
            <option value="">Semua tingkat</option>
            @foreach ($daftarTingkat as $t)
                <option value="{{ $t->id }}" @selected($filter['tingkat_id'] == $t->id)>Kelas {{ $t->romawi }}</option>
            @endforeach
        </select>
        @if ($filter['jurusan_id'] || $filter['tingkat_id'])
            <a href="{{ route('admin.kelas.index') }}" class="btn">Reset</a>
        @endif
    </form>

    @if ($kelas->isEmpty())
        <div class="empty">
            @include('admin.partials.icon', ['name' => 'inbox'])
            {{ ($filter['jurusan_id'] || $filter['tingkat_id']) ? 'Tidak ada kelas pada filter ini.' : 'Belum ada kelas. Klik "Tambah Kelas" untuk membuat.' }}
        </div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                <tr><th>#</th><th>Nama Kelas</th><th>Tingkat</th><th>Jurusan</th><th>Rombel</th><th>Siswa</th><th class="text-right">Aksi</th></tr>
                </thead>
                <tbody>
                @foreach ($kelas as $i => $k)
                    <tr>
                        <td class="muted">{{ $kelas->firstItem() + $i }}</td>
                        <td><b>{{ $k->nama }}</b></td>
                        <td>{{ $k->tingkat?->romawi }}</td>
                        <td>{{ $k->jurusan?->nama }}</td>
                        <td>{{ $k->nomor }}</td>
                        <td>
                            <a href="{{ route('admin.siswa.index', ['kelas_id' => $k->id, 'jurusan_id' => $k->jurusan_id, 'tingkat_id' => $k->tingkat_id]) }}">{{ $k->siswa_count }} siswa</a>
                        </td>
                        <td>
                            <div class="actions">
                                <a href="{{ route('admin.kelas.edit', $k) }}" class="btn btn-sm">@include('admin.partials.icon', ['name' => 'edit']) Edit</a>
                                <form method="POST" action="{{ route('admin.kelas.destroy', $k) }}" class="inline"
                                      onsubmit="return confirm('Hapus kelas {{ addslashes($k->nama) }}?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm" @disabled($k->siswa_count > 0)
                                            @style(["opacity:.45;cursor:not-allowed" => $k->siswa_count > 0])
                                            title="{{ $k->siswa_count > 0 ? 'Kelas masih berisi siswa' : 'Hapus kelas' }}">
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

    {{ $kelas->links('admin.partials.pagination') }}
</div>
@endsection
