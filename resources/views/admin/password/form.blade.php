@extends('admin.layouts.app')

@section('title', 'Reset Password')
@section('heading', 'Reset Password ' . $pengguna->name)
@section('subheading', ($daftarRole[$pengguna->role] ?? $pengguna->role) . ' · ' . $pengguna->email)

@section('actions')
    <a href="{{ route('admin.password.index') }}" class="btn">@include('admin.partials.icon', ['name' => 'back']) Kembali</a>
@endsection

@section('content')
<div class="alert alert-info">
    Setelah password direset, pengguna otomatis keluar dari semua perangkat dan harus login ulang dengan password baru. Sampaikan password baru secara langsung kepada pengguna.
</div>

<div class="card" style="max-width:760px">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.password.update', $pengguna) }}" novalidate
              onsubmit="return confirm('Reset password {{ addslashes($pengguna->name) }}?')">
            @csrf
            @method('PUT')

            <div class="form-grid">
                <div class="field">
                    <label for="password">Password Baru</label>
                    <input id="password" name="password" type="password" class="input @error('password') is-invalid @enderror"
                           autocomplete="new-password" required>
                    <span class="hint">Minimal 6 karakter.</span>
                    @error('password') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="field">
                    <label for="password_confirmation">Konfirmasi Password Baru</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" class="input"
                           autocomplete="new-password" required>
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('admin.password.index') }}" class="btn">Batal</a>
                <button type="submit" class="btn btn-primary">@include('admin.partials.icon', ['name' => 'key']) Reset Password</button>
            </div>
        </form>
    </div>
</div>
@endsection
