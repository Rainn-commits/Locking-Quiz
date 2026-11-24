@extends('admin.layouts.app')

@section('title', 'Data Siswa')
@section('heading', 'Data Siswa')
@section('subheading', 'Biodata siswa yang diisi melalui aplikasi mobile.')

@section('content')
<div class="card">
    <form method="GET" class="toolbar">
        <input type="search" name="q" value="{{ $filter['q'] }}" class="input grow" placeholder="Cari nama atau NIS/NISN…">

        <select name="jurusan_id" class="input" style="width:auto" onchange="this.form.kelas_id.value='';this.form.submit()">
            <option value="">Semua jurusan</option>
            @foreach ($jurusan as $j)
                <option value="{{ $j->id }}" @selected($filter['jurusan_id'] == $j->id)>{{ $j->nama }}</option>
            @endforeach
        </select>

        <select name="tingkat_id" class="input" style="width:auto" onchange="this.form.kelas_id.value='';this.form.submit()">
            <option value="">Semua tingkat</option>
            @foreach ($tingkat as $t)
                <option value="{{ $t->id }}" @selected($filter['tingkat_id'] == $t->id)>Kelas {{ $t->romawi }}</option>
            @endforeach
        </select>

        <select name="kelas_id" class="input" style="width:auto">
            <option value="">Semua rombel</option>
            @foreach ($kelas as $k)
                <option value="{{ $k->id }}" @selected($filter['kelas_id'] == $k->id)>{{ $k->nama }}</option>
            @endforeach
        </select>

        <select name="status" class="input" style="width:auto">
            <option value="">Semua status</option>
            <option value="lengkap" @selected($filter['status'] === 'lengkap')>Biodata lengkap</option>
            <option value="belum" @selected($filter['status'] === 'belum')>Belum lengkap</option>
        </select>

        <button class="btn btn-navy">@include('admin.partials.icon', ['name' => 'search']) Filter</button>
        @if (array_filter($filter))
            <a href="{{ route('admin.siswa.index') }}" class="btn">Reset</a>
        @endif
    </form>

    @if ($siswa->isEmpty())
        <div class="empty">@include('admin.partials.icon', ['name' => 'inbox'])Tidak ada data siswa.</div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                <tr><th>Nama</th><th>NIS/NISN</th><th>Jurusan</th><th>Kelas</th><th>Progres Biodata</th><th>Status</th><th class="text-right">Aksi</th></tr>
                </thead>
                <tbody>
                @foreach ($siswa as $s)
                    @php $persen = (int) $s->langkah * 20; @endphp
                    <tr>
                        <td>
                            <b>{{ $s->nama_lengkap ?? '(belum diisi)' }}</b>
                            <span class="sub">{{ $s->email ?? 'ID #' . $s->id }}</span>
                        </td>
                        <td>{{ $s->nis ?? '—' }}</td>
                        <td>{{ $s->jurusan ?? '—' }}</td>
                        <td>{{ $s->kelas ?? ($s->tingkat ? 'Kelas ' . $s->tingkat : '—') }}</td>
                        <td>
                            <div class="progress-row">
                                <div class="progress {{ $persen === 100 ? 'full' : '' }}" style="flex:1"><span style="width: {{ $persen }}%"></span></div>
                                <small>{{ $persen }}%</small>
                            </div>
                        </td>
                        <td>
                            @if ($s->is_banned)
                                <span class="badge badge-merah">Banned</span>
                            @elseif ($s->biodata_lengkap)
                                <span class="badge badge-hijau">Lengkap</span>
                            @else
                                <span class="badge badge-kuning">Belum lengkap</span>
                            @endif
                        </td>
                        <td>
                            <div class="actions">
                                <form method="POST" action="{{ route('admin.siswa.destroy', $s->id) }}" class="inline"
                                      onsubmit="return confirm('Hapus biodata siswa ini? Tindakan ini tidak bisa dibatalkan.')">
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

    {{ $siswa->links('admin.partials.pagination') }}
</div>
@endsection
