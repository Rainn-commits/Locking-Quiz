<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · Admin Locking Quiz</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body>
@php
    $jumlahBanned = \App\Models\User::where('role', 'siswa')->where('is_banned', true)->count();
    $admin = auth()->user();
@endphp
<div class="shell">
    <aside class="sidebar">
        <div class="brand">
            <div class="brand-logo">@include('admin.partials.icon', ['name' => 'lock'])</div>
            <div>
                <b>LOCKING QUIZ</b>
                <small>Panel Admin</small>
            </div>
        </div>

        <nav class="nav">
            <div class="nav-label">Menu</div>
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                @include('admin.partials.icon', ['name' => 'home']) Dashboard
            </a>
            <div class="nav-label">Pengguna</div>
            <a href="{{ route('admin.guru.index') }}" class="{{ request()->routeIs('admin.guru.*') ? 'active' : '' }}">
                @include('admin.partials.icon', ['name' => 'teacher']) Akun Guru
            </a>
            <a href="{{ route('admin.siswa.index') }}" class="{{ request()->routeIs('admin.siswa.*') ? 'active' : '' }}">
                @include('admin.partials.icon', ['name' => 'users']) Data Siswa
            </a>
            <div class="nav-label">Keamanan</div>
            <a href="{{ route('admin.banned.index') }}" class="{{ request()->routeIs('admin.banned.*') ? 'active' : '' }}">
                @include('admin.partials.icon', ['name' => 'shield']) Siswa Banned
                @if ($jumlahBanned > 0)
                    <span class="pill">{{ $jumlahBanned }}</span>
                @endif
            </a>
        </nav>

        <div class="sidebar-foot">
            <div class="user-box">
                <div class="avatar">{{ mb_strtoupper(mb_substr($admin->name ?? 'A', 0, 1)) }}</div>
                <div>
                    <b>{{ $admin->name }}</b>
                    <small>{{ $admin->email }}</small>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="btn btn-ghost btn-sm">
                    @include('admin.partials.icon', ['name' => 'logout']) Logout
                </button>
            </form>
        </div>
    </aside>

    <main class="main">
        <header class="topbar">
            <div>
                <h1>@yield('heading')</h1>
                @hasSection('subheading')
                    <p>@yield('subheading')</p>
                @endif
            </div>
            <div>@yield('actions')</div>
        </header>

        <section class="content">
            @include('admin.partials.flash')
            @yield('content')
        </section>
    </main>
</div>
</body>
</html>
