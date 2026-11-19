<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\GuruController;
use App\Http\Controllers\Admin\SiswaBannedController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BiodataController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:10,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::prefix('biodata')->where(['id' => '[0-9]+'])->group(function () {
    Route::post('/', [BiodataController::class, 'mulai']);                // start_screens ("Mulai")
    Route::get('/{id}', [BiodataController::class, 'show']);             // cek progres / layar selesai
    Route::put('/{id}/nama', [BiodataController::class, 'simpanNama']);       // identity_screen
    Route::put('/{id}/nis', [BiodataController::class, 'simpanNis']);         // nomor_induk_screen
    Route::put('/{id}/jurusan', [BiodataController::class, 'simpanJurusan']); // jurusan_screen
    Route::put('/{id}/tingkat', [BiodataController::class, 'simpanTingkat']); // kelas_screen
    Route::get('/{id}/kelas', [BiodataController::class, 'kelas']);           // daftar rombel
    Route::put('/{id}/kelas', [BiodataController::class, 'simpanKelas']);     // pilih rombel
});

Route::get('/jurusan', [BiodataController::class, 'jurusan']);       // jurusan_screen
Route::get('/tingkat-kelas', [BiodataController::class, 'tingkat']); // kelas_screen

Route::prefix('admin')->group(function () {
    Route::post('login', [AdminAuthController::class, 'login'])->middleware('throttle:10,1');

    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::post('logout', [AdminAuthController::class, 'logout']);

        Route::apiResource('guru', GuruController::class)->except('show');

        Route::get('siswa-banned', [SiswaBannedController::class, 'index']);
        Route::post('siswa/{siswa}/unlock', [SiswaBannedController::class, 'unlock']);
    });
});
