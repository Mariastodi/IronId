<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'dashboard');
Route::view('/kiosk', 'kiosk');
Route::view('/enroll', 'enroll');
