<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\{AuthController, GuruController, SiswaBannedController};

Route::get('/', function () {
    return view('welcome');
});



/*
|--------------------------------------------------------------------------
| Panel Admin (web, session) — buka: http://localhost:8000/admin
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [\App\Http\Controllers\Panel\AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [\App\Http\Controllers\Panel\AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.submit');

    Route::middleware(\App\Http\Middleware\EnsureAdminWeb::class)->group(function () {
        Route::post('logout', [\App\Http\Controllers\Panel\AuthController::class, 'logout'])->name('logout');
        Route::get('/', \App\Http\Controllers\Panel\DashboardController::class)->name('dashboard');
        Route::resource('guru', \App\Http\Controllers\Panel\GuruController::class)->except('show');
        Route::get('siswa', [\App\Http\Controllers\Panel\SiswaController::class, 'index'])->name('siswa.index');
        Route::delete('siswa/{id}', [\App\Http\Controllers\Panel\SiswaController::class, 'destroy'])->whereNumber('id')->name('siswa.destroy');
        Route::get('siswa-banned', [\App\Http\Controllers\Panel\SiswaBannedController::class, 'index'])->name('banned.index');
        Route::post('siswa-banned/unlock-semua', [\App\Http\Controllers\Panel\SiswaBannedController::class, 'unlockAll'])->name('banned.unlock-all');
        Route::post('siswa-banned/{siswa}/unlock', [\App\Http\Controllers\Panel\SiswaBannedController::class, 'unlock'])->name('banned.unlock');
    });
});
