@extends('admin.layouts.app')

@section('title', 'Impor Siswa')
@section('heading', 'Impor Siswa')
@section('subheading', 'Buat banyak akun siswa sekaligus dari file CSV. Kelas (rombel) yang belum ada dibuat otomatis.')

@section('actions')
    <a href="{{ route('admin.impor-siswa.template') }}" class="btn">@include('admin.partials.icon', ['name' => 'download']) Unduh Template CSV</a>
@endsection

@section('content')
@if (session('imporErrors'))
    <div class="alert alert-error" style="display:block">
        <b>Impor dibatalkan, tidak ada data yang disimpan.</b> Perbaiki kesalahan berikut lalu unggah ulang:
        <ul style="margin:8px 0 0 18px">
            @foreach (array_slice(session('imporErrors'), 0, 50) as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
        @if (count(session('imporErrors')) > 50)
            <div style="margin-top:6px">… dan {{ count(session('imporErrors')) - 50 }} kesalahan lainnya.</div>
        @endif
    </div>
@endif

<div class="card" style="max-width:760px;margin-bottom:18px">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.impor-siswa.store') }}" enctype="multipart/form-data" novalidate>
            @csrf
            <div class="field">
                <label for="file">File CSV</label>
                <input id="file" name="file" type="file" accept=".csv,text/csv" class="input @error('file') is-invalid @enderror" required>
                <span class="hint">Maksimal 2 MB dan {{ $maksBaris }} siswa per file. Dari Excel: File → Save As → CSV.</span>
                @error('file') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="form-actions">
                <a href="{{ route('admin.siswa.index') }}" class="btn">Batal</a>
                <button type="submit" class="btn btn-primary">@include('admin.partials.icon', ['name' => 'upload']) Impor</button>
            </div>
        </form>
    </div>
</div>

<div class="card" style="max-width:760px">
    <div class="card-body">
        <h3 style="margin:0 0 10px">Format kolom</h3>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Kolom</th><th>Isi</th><th>Contoh</th></tr></thead>
                <tbody>
                <tr><td><b>nama</b></td><td>Nama lengkap siswa</td><td>Budi Santoso</td></tr>
                <tr><td><b>email</b></td><td>Email untuk login, belum terdaftar</td><td>budi@contoh.sch.id</td></tr>
                <tr><td><b>nis</b></td><td>NIS/NISN, 4–20 digit angka, belum terdaftar</td><td>1234567890</td></tr>
                <tr><td><b>jurusan</b></td><td>Kode atau nama jurusan yang terdaftar</td><td>{{ $daftarJurusan->pluck('kode')->implode(', ') ?: '—' }}</td></tr>
                <tr><td><b>tingkat</b></td><td>X, XI, XII (atau 10, 11, 12)</td><td>X</td></tr>
                <tr><td><b>rombel</b></td><td>Nomor rombel 1–99</td><td>1</td></tr>
                <tr><td>password <span class="muted">(opsional)</span></td><td>Minimal 6 karakter. Jika kosong, password = NIS</td><td>rahasia123</td></tr>
                </tbody>
            </table>
        </div>
        <p class="muted" style="margin:10px 0 0">
            Baris pertama file harus berisi judul kolom. Pemisah koma (,) atau titik koma (;) sama-sama bisa dipakai.
            Jika ada satu baris yang salah, seluruh impor dibatalkan supaya data tidak setengah masuk.
        </p>
    </div>
</div>
@endsection
