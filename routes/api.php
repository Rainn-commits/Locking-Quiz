<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\GuruController;
use App\Http\Controllers\Admin\SiswaBannedController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BiodataController;
use App\Http\Controllers\Api\UnlockTokenController;
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
    Route::post('/', [BiodataController::class, 'mulai']);                
    Route::get('/{id}', [BiodataController::class, 'show']);             
    Route::put('/{id}/nama', [BiodataController::class, 'simpanNama']);       
    Route::put('/{id}/nis', [BiodataController::class, 'simpanNis']);         
    Route::put('/{id}/jurusan', [BiodataController::class, 'simpanJurusan']); 
    Route::put('/{id}/tingkat', [BiodataController::class, 'simpanTingkat']); 
    Route::get('/{id}/kelas', [BiodataController::class, 'kelas']);           
    Route::put('/{id}/kelas', [BiodataController::class, 'simpanKelas']);     
});

Route::get('/jurusan', [BiodataController::class, 'jurusan']);       
Route::get('/tingkat-kelas', [BiodataController::class, 'tingkat']); 

Route::prefix('admin')->group(function () {
    Route::post('login', [AdminAuthController::class, 'login'])->middleware('throttle:10,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AdminAuthController::class, 'logout'])->middleware('role:admin,guru');

        Route::middleware('role:admin,guru')->group(function () {
            Route::get('siswa-banned', [SiswaBannedController::class, 'index']);
            Route::post('siswa/{siswa}/unlock-token', [SiswaBannedController::class, 'issueToken']);
            Route::post('siswa/{siswa}/override', [SiswaBannedController::class, 'override']);
        });

        Route::middleware('role:admin')->group(function () {
            Route::apiResource('guru', GuruController::class)->except('show');
            Route::patch('guru/{guru}/verify', [GuruController::class, 'verify']);
            Route::get('audit-logs', [AuditLogController::class, 'index']);
        });
    });
});

Route::middleware(['auth:sanctum', 'role:siswa', 'throttle:5,1'])
    ->post('/unlock-token/redeem', [UnlockTokenController::class, 'redeem']);
