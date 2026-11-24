<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Admin · Locking Quiz</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body>
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-logo" style="color:#fff">@include('admin.partials.icon', ['name' => 'lock'])</div>
        <h1>Halo, Admin!</h1>
        <p class="lead">Masuk untuk mengelola Locking Quiz</p>

        @include('admin.partials.flash')

        <form method="POST" action="{{ route('admin.login.submit') }}" novalidate>
            @csrf
            <div class="field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" class="input @error('email') is-invalid @enderror"
                       value="{{ old('email') }}" placeholder="admin@lockingquiz.test" autocomplete="email" autofocus required>
                @error('email') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" class="input @error('password') is-invalid @enderror"
                       placeholder="Masukkan password" autocomplete="current-password" required>
                @error('password') <span class="error">{{ $message }}</span> @enderror
            </div>

            <label class="checkbox" style="margin-bottom:18px">
                <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}> Ingat saya
            </label>

            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>
    </div>
</div>
</body>
</html>
