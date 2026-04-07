<?php

use App\Http\Controllers\AuthUserController;
use Illuminate\Support\Facades\Route;

// ── Public ───────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthUserController::class, 'register']);
    Route::post('/login',    [AuthUserController::class, 'login']);
});

// ── Protected ────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthUserController::class, 'logout']);
        Route::get('/me',      [AuthUserController::class, 'me']);
    });
});
