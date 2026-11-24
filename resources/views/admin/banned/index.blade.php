@extends('admin.layouts.app')

@section('title', 'Siswa Banned')
@section('heading', 'Siswa Banned')
@section('subheading', 'Siswa yang diblokir otomatis oleh Smart Lock System karena melebihi batas pelanggaran.')

@section('actions')
    @if ($banned->total() > 0)
        <form method="POST" action="{{ route('admin.banned.unlock-all') }}" class="inline"
              onsubmit="return confirm('Unlock SEMUA siswa yang diblokir ({{ $banned->total() }} siswa)?')">
            @csrf
            <button class="btn btn-success">@include('admin.partials.icon', ['name' => 'unlock']) Unlock Semua</button>
        </form>
    @endif
@endsection

@section('content')
<div class="alert alert-info">
    🔒 Unlock akan membuka blokir dan mereset jumlah pelanggaran siswa menjadi 0, sehingga siswa dapat login dan mengerjakan kuis kembali.
</div>

<div class="card">
    <form method="GET" class="toolbar">
        <input type="search" name="q" value="{{ $q }}" class="input grow" placeholder="Cari nama, email, atau NIS…">
        <button class="btn btn-navy">@include('admin.partials.icon', ['name' => 'search']) Cari</button>
        @if ($q !== '')
            <a href="{{ route('admin.banned.index') }}" class="btn">Reset</a>
        @endif
    </form>

    @if ($banned->isEmpty())
        <div class="empty">
            @include('admin.partials.icon', ['name' => 'check'])
            {{ $q !== '' ? 'Tidak ada siswa banned yang cocok.' : 'Tidak ada siswa yang sedang diblokir. 🎉' }}
        </div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                <tr><th>Siswa</th><th>NIS/NISN</th><th>Kelas</th><th>Pelanggaran</th><th>Diblokir</th><th class="text-right">Aksi</th></tr>
                </thead>
                <tbody>
                @foreach ($banned as $s)
                    <tr>
                        <td>
                            <b>{{ $s->nama_lengkap ?? $s->name }}</b>
                            <span class="sub">{{ $s->email ?? '—' }}</span>
                        </td>
                        <td>{{ $s->nis ?? '—' }}</td>
                        <td>{{ $s->kelas ?? '—' }}</td>
                        <td><span class="badge badge-merah">{{ $s->jumlah_pelanggaran }}x pelanggaran</span></td>
                        <td>
                            {{ $s->banned_at?->translatedFormat('d M Y, H:i') ?? '—' }}
                            <span class="sub">{{ $s->banned_at?->diffForHumans() }}</span>
                        </td>
                        <td>
                            <div class="actions">
                                <form method="POST" action="{{ route('admin.banned.unlock', $s->id) }}" class="inline"
                                      onsubmit="return confirm('Unlock {{ addslashes($s->nama_lengkap ?? $s->name) }}?')">
                                    @csrf
                                    <button class="btn btn-success btn-sm">@include('admin.partials.icon', ['name' => 'unlock']) Unlock</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{ $banned->links('admin.partials.pagination') }}
</div>
@endsection
