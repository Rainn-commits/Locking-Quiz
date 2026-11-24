<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Penjaga halaman panel admin (web/session).
 * Berbeda dengan EnsureRole yang membalas JSON untuk API,
 * middleware ini mengarahkan kembali ke halaman login.
 */
class EnsureAdminWeb
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('web')->user();

        if (! $user) {
            return redirect()->route('admin.login');
        }

        if ($user->role !== 'admin' || $user->status !== 'active') {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login')
                ->withErrors(['email' => 'Akun ini tidak memiliki akses admin atau tidak aktif.']);
        }

        return $next($request);
    }
}
