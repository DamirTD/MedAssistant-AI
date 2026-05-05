<?php

use App\Http\Controllers\Api\DiagnosisController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DiagnosisHistoryController;
use App\Http\Controllers\Api\PortalController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;

Route::post('/diagnosis/analyze', [DiagnosisController::class, 'analyze']);

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/portal', [PortalController::class, 'show']);
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::patch('/profile/email', [ProfileController::class, 'updateEmail']);
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword']);
    Route::get('/history', [DiagnosisHistoryController::class, 'index']);
    Route::get('/history/{id}', [DiagnosisHistoryController::class, 'show']);
});
