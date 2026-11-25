@extends('admin.layouts.app')

@php
    $tgl = fn ($v) => $v ? \Illuminate\Support\Carbon::parse($v)->translatedFormat('d M Y, H:i') : '—';
@endphp

@section('title', 'Detail Kuis')
@section('heading', $kuis->title)
@section('subheading', $kuis->subject . ' · dibuat oleh ' . ($kuis->creator?->name ?? 'akun yang sudah dihapus'))

@section('actions')
    <a href="{{ route('admin.riwayat-kuis.index') }}" class="btn">@include('admin.partials.icon', ['name' => 'back']) Kembali</a>
@endsection

@section('content')
<div class="card" style="margin-bottom:18px">
    <div class="card-body">
        <div class="table-wrap">
            <table>
                <tbody>
                <tr><td style="width:200px" class="muted">Status</td>
                    <td><span class="badge {{ $kuis->status === 'published' ? 'badge-hijau' : 'badge-kuning' }}">{{ $daftarStatus[$kuis->status] ?? $kuis->status }}</span></td></tr>
                <tr><td class="muted">Guru</td><td>{{ $kuis->creator?->name ?? '—' }} <span class="muted">{{ $kuis->creator?->email }}</span></td></tr>
                <tr><td class="muted">Mulai</td><td>{{ $tgl($kuis->start_at) }}</td></tr>
                <tr><td class="muted">Selesai</td><td>{{ $tgl($kuis->end_at) }}</td></tr>
                <tr><td class="muted">Durasi</td><td>{{ $kuis->duration_minutes ? $kuis->duration_minutes . ' menit' : '—' }}</td></tr>
                <tr><td class="muted">Jumlah soal</td><td>{{ $kuis->questions->count() }}</td></tr>
                <tr><td class="muted">Dibuat / diubah</td><td>{{ $tgl($kuis->created_at) }} / {{ $tgl($kuis->updated_at) }}</td></tr>
                @if ($kuis->description)
                    <tr><td class="muted">Deskripsi</td><td>{{ $kuis->description }}</td></tr>
                @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="alert alert-info">
    Data peserta, nilai, dan pelanggaran akan tampil di sini setelah fitur pengerjaan kuis oleh siswa tersedia.
</div>

<div class="card">
    <div class="card-body">
        <h3 style="margin:0 0 12px">Daftar Soal</h3>
        @forelse ($kuis->questions as $n => $soal)
            <div style="padding:12px 0;border-top:1px solid var(--garis)">
                <div><b>{{ $n + 1 }}.</b> {{ $soal->prompt }}</div>
                @if ($soal->image_url)
                    <img src="{{ $soal->image_url }}" alt="Gambar soal {{ $n + 1 }}" style="max-width:280px;margin-top:8px;border-radius:8px">
                @endif
                <ol type="A" style="margin:8px 0 0 22px">
                    @foreach ($soal->options as $opsi)
                        <li @if ($opsi->is_correct) style="font-weight:600;color:#17734d" @endif>
                            {{ $opsi->option_text }}
                            @if ($opsi->is_correct)
                                <span class="badge badge-hijau">Jawaban benar</span>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </div>
        @empty
            <div class="empty">
                @include('admin.partials.icon', ['name' => 'inbox'])
                Kuis ini belum memiliki soal.
            </div>
        @endforelse
    </div>
</div>
@endsection
