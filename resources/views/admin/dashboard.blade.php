@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')
@section('subheading', 'Halo, ' . auth()->user()->name . '! Ini ringkasan sistem Locking Quiz hari ini.')

@section('content')
<div class="grid grid-4" style="margin-bottom:18px">
    <div class="card stat">
        <div class="stat-icon ungu">@include('admin.partials.icon', ['name' => 'teacher'])</div>
        <div><small>Akun Guru</small><b>{{ number_format($stats['guru']) }}</b></div>
    </div>
    <div class="card stat">
        <div class="stat-icon koral">@include('admin.partials.icon', ['name' => 'users'])</div>
        <div><small>Total Siswa</small><b>{{ number_format($stats['siswa']) }}</b></div>
    </div>
    <div class="card stat">
        <div class="stat-icon hijau">@include('admin.partials.icon', ['name' => 'check'])</div>
        <div><small>Biodata Lengkap</small><b>{{ number_format($stats['biodata_lengkap']) }}</b></div>
    </div>
    <div class="card stat">
        <div class="stat-icon merah">@include('admin.partials.icon', ['name' => 'shield'])</div>
        <div><small>Siswa Banned</small><b>{{ number_format($stats['banned']) }}</b></div>
    </div>
</div>

<div class="grid grid-2" style="margin-bottom:18px">
    <div class="card">
        <div class="card-head">
            <h2>Siswa Banned Terbaru</h2>
            <a href="{{ route('admin.banned.index') }}" class="btn btn-sm">Lihat semua</a>
        </div>
        @if ($bannedTerbaru->isEmpty())
            <div class="empty">@include('admin.partials.icon', ['name' => 'check'])Tidak ada siswa yang diblokir. Aman! 🎉</div>
        @else
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Siswa</th><th>Pelanggaran</th><th class="text-right">Aksi</th></tr></thead>
                    <tbody>
                    @foreach ($bannedTerbaru as $s)
                        <tr>
                            <td>{{ $s->name }}<span class="sub">{{ $s->banned_at?->diffForHumans() ?? '-' }}</span></td>
                            <td><span class="badge badge-merah">{{ $s->jumlah_pelanggaran }}x</span></td>
                            <td class="text-right">
                                <form method="POST" action="{{ route('admin.banned.unlock', $s) }}" class="inline"
                                      onsubmit="return confirm('Unlock {{ addslashes($s->name) }}?')">
                                    @csrf
                                    <button class="btn btn-success btn-sm">@include('admin.partials.icon', ['name' => 'unlock']) Unlock</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="card">
        <div class="card-head"><h2>Sebaran Siswa per Jurusan</h2></div>
        <div class="card-body">
            @if ($perJurusan->isEmpty())
                <div class="empty">Belum ada data jurusan. Jalankan <code>php artisan db:seed</code>.</div>
            @else
                <div class="bar-list">
                    @foreach ($perJurusan as $j)
                        <div class="bar-item">
                            <div class="label"><span>{{ $j->nama }}</span><b>{{ $j->total }} siswa</b></div>
                            <div class="progress"><span style="width: {{ round($j->total / $maxJurusan * 100) }}%"></span></div>
                        </div>
                    @endforeach
                </div>
                <p class="muted" style="margin:18px 0 0;font-size:12px">
                    {{ $stats['siswa'] - $stats['biodata_lengkap'] }} siswa belum menyelesaikan pengisian biodata.
                </p>
            @endif
        </div>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <h2>Siswa Baru Mendaftar</h2>
        <a href="{{ route('admin.siswa.index') }}" class="btn btn-sm">Lihat semua</a>
    </div>
    @if ($siswaTerbaru->isEmpty())
        <div class="empty">@include('admin.partials.icon', ['name' => 'inbox'])Belum ada siswa yang mengisi biodata.</div>
    @else
        <div class="table-wrap">
            <table>
                <thead><tr><th>Nama</th><th>NIS/NISN</th><th>Jurusan</th><th>Kelas</th><th>Progres</th></tr></thead>
                <tbody>
                @foreach ($siswaTerbaru as $s)
                    @php $persen = (int) $s->langkah * 20; @endphp
                    <tr>
                        <td>{{ $s->nama_lengkap ?? '—' }}<span class="sub">{{ \Illuminate\Support\Carbon::parse($s->created_at)->diffForHumans() }}</span></td>
                        <td>{{ $s->nis ?? '—' }}</td>
                        <td>{{ $s->jurusan ?? '—' }}</td>
                        <td>{{ $s->kelas ?? '—' }}</td>
                        <td>
                            <div class="progress-row">
                                <div class="progress {{ $persen === 100 ? 'full' : '' }}" style="flex:1"><span style="width: {{ $persen }}%"></span></div>
                                <small>{{ $persen }}%</small>
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
