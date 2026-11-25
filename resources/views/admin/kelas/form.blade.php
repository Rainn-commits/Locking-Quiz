@extends('admin.layouts.app')

@php
    $edit = $kelas->exists;
    $terkunci = $edit && ($jumlahSiswa ?? 0) > 0;
@endphp

@section('title', $edit ? 'Edit Kelas' : 'Tambah Kelas')
@section('heading', $edit ? 'Edit Kelas ' . $kelas->nama : 'Tambah Kelas')
@section('subheading', 'Nama kelas dibuat otomatis dari tingkat, jurusan, dan nomor rombel.')

@section('actions')
    <a href="{{ route('admin.kelas.index') }}" class="btn">@include('admin.partials.icon', ['name' => 'back']) Kembali</a>
@endsection

@section('content')
@if ($terkunci)
    <div class="alert alert-info">
        Kelas ini sudah berisi {{ $jumlahSiswa }} siswa, jadi jurusan dan tingkatnya tidak bisa diubah. Nomor rombel masih bisa diganti.
    </div>
@endif

<div class="card" style="max-width:760px">
    <div class="card-body">
        <form method="POST" action="{{ $edit ? route('admin.kelas.update', $kelas) : route('admin.kelas.store') }}" novalidate>
            @csrf
            @if ($edit) @method('PUT') @endif

            <div class="form-grid">
                <div class="field">
                    <label for="tingkat_id">Tingkat</label>
                    @if ($terkunci)
                        <input type="hidden" name="tingkat_id" value="{{ $kelas->tingkat_id }}">
                    @endif
                    <select id="tingkat_id" name="tingkat_id" class="input @error('tingkat_id') is-invalid @enderror" @disabled($terkunci)>
                        <option value="">Pilih tingkat</option>
                        @foreach ($daftarTingkat as $t)
                            <option value="{{ $t->id }}" @selected(old('tingkat_id', $kelas->tingkat_id) == $t->id)>Kelas {{ $t->romawi }}</option>
                        @endforeach
                    </select>
                    @error('tingkat_id') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="field">
                    <label for="jurusan_id">Jurusan</label>
                    @if ($terkunci)
                        <input type="hidden" name="jurusan_id" value="{{ $kelas->jurusan_id }}">
                    @endif
                    <select id="jurusan_id" name="jurusan_id" class="input @error('jurusan_id') is-invalid @enderror" @disabled($terkunci)>
                        <option value="">Pilih jurusan</option>
                        @foreach ($daftarJurusan as $j)
                            <option value="{{ $j->id }}" @selected(old('jurusan_id', $kelas->jurusan_id) == $j->id)>{{ $j->nama }}</option>
                        @endforeach
                    </select>
                    @error('jurusan_id') <span class="error">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="field" style="max-width:240px">
                <label for="nomor">Nomor Rombel</label>
                <input id="nomor" name="nomor" type="number" min="1" max="99"
                       class="input @error('nomor') is-invalid @enderror"
                       value="{{ old('nomor', $kelas->nomor) }}" placeholder="cth. 1" required>
                <span class="hint">Angka 1–99, cth. 1 untuk "X Perbankan 1".</span>
                @error('nomor') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="form-actions">
                <a href="{{ route('admin.kelas.index') }}" class="btn">Batal</a>
                <button type="submit" class="btn btn-primary">{{ $edit ? 'Simpan Perubahan' : 'Tambah Kelas' }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
