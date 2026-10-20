<?php
// Taruh di routes/api.php (ganti blok auth lama, termasuk baris komentar yang rusak)

use App\Http\Controllers\Api\AuthController;
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