@extends('admin.layouts.app')

@php $edit = $jurusan->exists; @endphp

@section('title', $edit ? 'Edit Jurusan' : 'Tambah Jurusan')
@section('heading', $edit ? 'Edit Jurusan' : 'Tambah Jurusan')
@section('subheading', $edit ? 'Perbarui data jurusan ' . $jurusan->nama . '. Nama semua kelas di jurusan ini ikut menyesuaikan.' : 'Jurusan baru akan muncul di pilihan biodata siswa.')

@section('actions')
    <a href="{{ route('admin.jurusan.index') }}" class="btn">@include('admin.partials.icon', ['name' => 'back']) Kembali</a>
@endsection

@section('content')
<div class="card" style="max-width:760px">
    <div class="card-body">
        <form method="POST" action="{{ $edit ? route('admin.jurusan.update', $jurusan) : route('admin.jurusan.store') }}" novalidate>
            @csrf
            @if ($edit) @method('PUT') @endif

            <div class="form-grid">
                <div class="field">
                    <label for="kode">Kode</label>
                    <input id="kode" name="kode" class="input @error('kode') is-invalid @enderror"
                           value="{{ old('kode', $jurusan->kode) }}" placeholder="cth. PBK" maxlength="10" required>
                    <span class="hint">Singkatan, maksimal 10 karakter.</span>
                    @error('kode') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="field">
                    <label for="nama">Nama Jurusan</label>
                    <input id="nama" name="nama" class="input @error('nama') is-invalid @enderror"
                           value="{{ old('nama', $jurusan->nama) }}" placeholder="cth. Perbankan" required>
                    @error('nama') <span class="error">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('admin.jurusan.index') }}" class="btn">Batal</a>
                <button type="submit" class="btn btn-primary">{{ $edit ? 'Simpan Perubahan' : 'Tambah Jurusan' }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
