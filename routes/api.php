<?php

use App\Http\Controllers\AuthUserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
    HomeController,
    MarketplaceController,
    LocationController,
    AdController,
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
    Route::post('/ads',              [AdController::class, 'store']);
    Route::get('/ads/saved-ads',    [AdController::class, 'savedAds']);
    Route::post('/ads/{id}/contact', [AdController::class, 'contact']);
    Route::post('/ads/{id}/save',   [AdController::class, 'save']);
    Route::get('/ads/{id}',         [AdController::class, 'show']);
    Route::put('/ads/{id}',         [AdController::class, 'update']);
    Route::delete('/ads/{id}',      [AdController::class, 'destroy']);
});

Route::get('/home', [HomeController::class, 'index']);
Route::get('/ads/{id}', [AdController::class, 'show']);
Route::get('/marketplace/{slug}',      [MarketplaceController::class, 'show']);
Route::get('/marketplace/{slug}/ads',  [MarketplaceController::class, 'ads']);
Route::get('/cities',                  [LocationController::class, 'cities']);
Route::get('/cities/{cityId}/areas',   [LocationController::class, 'areas']);
