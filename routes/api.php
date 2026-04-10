<?php

use App\Http\Controllers\AuthUserController;
use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\Api\{
        HomeController,
        MarketplaceController,
        LocationController

        };

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

Route::get('/home', [HomeController::class, 'index']);

Route::get('/marketplace/{slug}',      [MarketplaceController::class, 'show']);
Route::get('/marketplace/{slug}/ads',  [MarketplaceController::class, 'ads']);
Route::get('/cities',                  [LocationController::class, 'cities']);
Route::get('/cities/{cityId}/areas',   [LocationController::class, 'areas']);
