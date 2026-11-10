<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BiodataController;
use Illuminate\Support\Facades\Route;

// Publik (dibatasi 10 request/menit agar tidak bisa di-brute-force)
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// Butuh token Sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Biodata siswa: TANPA token. Siswa dikenali dari {id} yang didapat saat menekan "Mulai".
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

// Data pilihan untuk layar (tanpa token)
Route::get('/jurusan', [BiodataController::class, 'jurusan']);       // jurusan_screen
Route::get('/tingkat-kelas', [BiodataController::class, 'tingkat']); // kelas_screen
