<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\{AuthController, GuruController, SiswaBannedController};

Route::get('/', function () {
    return view('welcome');
});

