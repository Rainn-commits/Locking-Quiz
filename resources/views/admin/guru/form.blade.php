@extends('admin.layouts.app')

@php $edit = $guru->exists; @endphp

@section('title', $edit ? 'Edit Guru' : 'Tambah Guru')
@section('heading', $edit ? 'Edit Akun Guru' : 'Tambah Akun Guru')
@section('subheading', $edit ? 'Perbarui data akun ' . $guru->name . '.' : 'Buat akun baru agar guru bisa login ke aplikasi.')

@section('actions')
    <a href="{{ route('admin.guru.index') }}" class="btn">@include('admin.partials.icon', ['name' => 'back']) Kembali</a>
@endsection

@section('content')
<div class="card" style="max-width:760px">
    <div class="card-body">
        <form method="POST" action="{{ $edit ? route('admin.guru.update', $guru) : route('admin.guru.store') }}" novalidate>
            @csrf
            @if ($edit) @method('PUT') @endif

            <div class="field">
                <label for="name">Nama Lengkap</label>
                <input id="name" name="name" class="input @error('name') is-invalid @enderror"
                       value="{{ old('name', $guru->name) }}" placeholder="cth. Budi Santoso, S.Pd." required>
                @error('name') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="form-grid">
                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" class="input @error('email') is-invalid @enderror"
                           value="{{ old('email', $guru->email) }}" placeholder="guru@sekolah.sch.id" required>
                    <span class="hint">Dipakai guru untuk login.</span>
                    @error('email') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="field">
                    <label for="status">Status Akun</label>
                    <select id="status" name="status" class="input @error('status') is-invalid @enderror">
                        @foreach ($daftarStatus as $val => $label)
                            <option value="{{ $val }}" @selected(old('status', $guru->status) === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <span class="hint">Hanya akun "Aktif" yang bisa login.</span>
                    @error('status') <span class="error">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="form-grid">
                <div class="field">
                    <label for="password">Password {{ $edit ? 'Baru' : '' }}</label>
                    <input id="password" name="password" type="password" class="input @error('password') is-invalid @enderror"
                           autocomplete="new-password" {{ $edit ? '' : 'required' }}>
                    <span class="hint">{{ $edit ? 'Kosongkan jika tidak ingin mengubah password.' : 'Minimal 6 karakter.' }}</span>
                    @error('password') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="field">
                    <label for="password_confirmation">Konfirmasi Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" class="input"
                           autocomplete="new-password" {{ $edit ? '' : 'required' }}>
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('admin.guru.index') }}" class="btn">Batal</a>
                <button type="submit" class="btn btn-primary">{{ $edit ? 'Simpan Perubahan' : 'Tambah Guru' }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
