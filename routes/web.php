<?php

use App\Http\Controllers\Panel;
use App\Http\Middleware\EnsureAdminWeb;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [Panel\AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [Panel\AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.submit');

    Route::middleware(EnsureAdminWeb::class)->group(function () {
        Route::post('logout', [Panel\AuthController::class, 'logout'])->name('logout');
        Route::get('/', Panel\DashboardController::class)->name('dashboard');

        Route::resource('guru', Panel\GuruController::class)->except('show');
        Route::get('siswa', [Panel\SiswaController::class, 'index'])->name('siswa.index');
        Route::delete('siswa/{id}', [Panel\SiswaController::class, 'destroy'])->whereNumber('id')->name('siswa.destroy');
        Route::get('impor-siswa', [Panel\ImporSiswaController::class, 'create'])->name('impor-siswa.create');
        Route::post('impor-siswa', [Panel\ImporSiswaController::class, 'store'])->name('impor-siswa.store');
        Route::get('impor-siswa/template', [Panel\ImporSiswaController::class, 'template'])->name('impor-siswa.template');

        Route::resource('jurusan', Panel\JurusanController::class)->except('show');
        Route::resource('kelas', Panel\KelasController::class)->except('show')->parameters(['kelas' => 'kelas']);

        Route::get('riwayat-kuis', [Panel\RiwayatKuisController::class, 'index'])->name('riwayat-kuis.index');
        Route::get('riwayat-kuis/{quiz}', [Panel\RiwayatKuisController::class, 'show'])->name('riwayat-kuis.show');

        Route::get('siswa-banned', [Panel\SiswaBannedController::class, 'index'])->name('banned.index');
        Route::post('siswa-banned/unlock-semua', [Panel\SiswaBannedController::class, 'unlockAll'])->name('banned.unlock-all');
        Route::post('siswa-banned/{siswa}/unlock', [Panel\SiswaBannedController::class, 'unlock'])->name('banned.unlock');
        Route::get('reset-password', [Panel\PasswordController::class, 'index'])->name('password.index');
        Route::get('reset-password/{user}', [Panel\PasswordController::class, 'edit'])->name('password.edit');
        Route::put('reset-password/{user}', [Panel\PasswordController::class, 'update'])->name('password.update');
        Route::get('audit-log', [Panel\AuditLogController::class, 'index'])->name('audit.index');
    });
});
