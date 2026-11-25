@extends('admin.layouts.app')

@section('title', 'Riwayat Kuis')
@section('heading', 'Riwayat Kuis')
@section('subheading', 'Semua kuis yang dibuat guru. Data peserta dan nilai akan tampil setelah fitur pengerjaan kuis siswa tersedia.')

@php
    $tgl = fn ($v) => $v ? \Illuminate\Support\Carbon::parse($v)->translatedFormat('d M Y, H:i') : '—';
    $adaFilter = $filter['q'] !== '' || $filter['status'] || $filter['creator_id'] || $filter['subject'] !== '';
@endphp

@section('content')
<div class="card">
    <form method="GET" class="toolbar">
        <input type="search" name="q" value="{{ $filter['q'] }}" class="input grow" placeholder="Cari judul kuis…">
        <select name="status" class="input" style="width:auto">
            <option value="">Semua status</option>
            @foreach ($daftarStatus as $val => $label)
                <option value="{{ $val }}" @selected($filter['status'] === $val)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="creator_id" class="input" style="width:auto">
            <option value="">Semua guru</option>
            @foreach ($daftarGuru as $g)
                <option value="{{ $g->id }}" @selected($filter['creator_id'] == $g->id)>{{ $g->name }}</option>
            @endforeach
        </select>
        <select name="subject" class="input" style="width:auto">
            <option value="">Semua mapel</option>
            @foreach ($daftarMapel as $m)
                <option value="{{ $m }}" @selected(mb_strtolower($filter['subject']) === mb_strtolower($m))>{{ $m }}</option>
            @endforeach
        </select>
        <button class="btn btn-navy">@include('admin.partials.icon', ['name' => 'search']) Cari</button>
        @if ($adaFilter)
            <a href="{{ route('admin.riwayat-kuis.index') }}" class="btn">Reset</a>
        @endif
    </form>

    @if ($kuis->isEmpty())
        <div class="empty">
            @include('admin.partials.icon', ['name' => 'inbox'])
            {{ $adaFilter ? 'Tidak ada kuis yang cocok dengan filter.' : 'Belum ada kuis yang dibuat guru.' }}
        </div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                <tr><th>#</th><th>Judul</th><th>Mapel</th><th>Guru</th><th>Soal</th><th>Jadwal</th><th>Status</th><th class="text-right">Aksi</th></tr>
                </thead>
                <tbody>
                @foreach ($kuis as $i => $k)
                    <tr>
                        <td class="muted">{{ $kuis->firstItem() + $i }}</td>
                        <td>
                            <b>{{ $k->title }}</b>
                            <span class="sub">Dibuat {{ $tgl($k->created_at) }}</span>
                        </td>
                        <td>{{ $k->subject }}</td>
                        <td>{{ $k->creator?->name ?? '—' }}</td>
                        <td>{{ $k->questions_count }}</td>
                        <td>
                            {{ $tgl($k->start_at) }}
                            @if ($k->duration_minutes)
                                <span class="sub">{{ $k->duration_minutes }} menit</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $k->status === 'published' ? 'badge-hijau' : 'badge-kuning' }}">{{ $daftarStatus[$k->status] ?? $k->status }}</span>
                        </td>
                        <td>
                            <div class="actions">
                                <a href="{{ route('admin.riwayat-kuis.show', $k) }}" class="btn btn-sm">@include('admin.partials.icon', ['name' => 'eye']) Detail</a>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{ $kuis->links('admin.partials.pagination') }}
</div>
@endsection
