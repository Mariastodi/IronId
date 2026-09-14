<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CheckInController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\FaceEnrollmentController;
use App\Http\Controllers\Api\FaceRecognitionController;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\PlanController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::post('/kiosk/recognize', [FaceRecognitionController::class, 'recognize'])->middleware(['role:admin,attendant', 'throttle:60,1']);

    Route::middleware('role:admin,attendant')->group(function () {
        Route::get('/dashboard', DashboardController::class);
        Route::apiResource('plans', PlanController::class);
        Route::apiResource('members', MemberController::class);
        Route::post('/members/{member}/face', [FaceEnrollmentController::class, 'store']);
        Route::delete('/members/{member}/face', [FaceEnrollmentController::class, 'destroy']);
        Route::post('/members/{member}/renew-plan', [MemberController::class, 'renewPlan']);
        Route::apiResource('check-ins', CheckInController::class)->only(['index', 'store']);
    });
});
