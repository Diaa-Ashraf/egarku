<?php

use App\Http\Controllers\AuthUserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
    HomeController,
    MarketplaceController,
    LocationController,
    AdController,
    VendorController,
    UserController,
    DashboardController,
    PaymentController,
    NotificationController,
    FeaturedAdsController,
};

// ── Public ───────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/register',          [AuthUserController::class, 'register']);
    Route::post('/login',             [AuthUserController::class, 'login']);
});


// ── Protected ────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/subscribe', [PaymentController::class, 'subscribe']);

    Route::get('/notifications',              [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/read-all',    [NotificationController::class, 'markAllRead']);
    Route::post('/notifications/{id}/read',   [NotificationController::class, 'markRead']);
    Route::delete('/notifications/{id}',      [NotificationController::class, 'destroy']);


    Route::prefix('auth')->group(function () {
        Route::post('/logout',        [AuthUserController::class, 'logout']);
        Route::get('/me',             [AuthUserController::class, 'me']);
    });
    Route::post('/ads',               [AdController::class, 'store']);
    Route::get('/ads/saved-ads',      [AdController::class, 'savedAds']);
    Route::post('/ads/{id}/contact',  [AdController::class, 'contact']);
    Route::post('/ads/{id}/save',     [AdController::class, 'save']);
    Route::get('/ads/{id}',           [AdController::class, 'show']);
    Route::put('/ads/{id}',           [AdController::class, 'update']);
    Route::delete('/ads/{id}',        [AdController::class, 'destroy']);
    Route::put('/vendor/profile',     [VendorController::class, 'update']);
    Route::get('/user/profile',       [UserController::class, 'profile']);
    Route::put('/user/profile',       [UserController::class, 'updateProfile']);
    Route::put('/user/password',      [UserController::class, 'updatePassword']);
    Route::post('/user/avatar',       [UserController::class, 'updateAvatar']);
    Route::delete('/user/account',    [UserController::class, 'deleteAccount']);
});
Route::middleware('auth:sanctum')->prefix('dashboard')->group(function () {
    Route::get('/stats',              [DashboardController::class, 'stats']);
    Route::get('/ads',                [DashboardController::class, 'myAds']);
    Route::get('/subscription',       [DashboardController::class, 'subscription']);
    Route::get('/transactions',       [DashboardController::class, 'transactions']);
    Route::get('/interactions',       [DashboardController::class, 'interactions']);
    Route::get('/analytics',          [DashboardController::class, 'analytics']);
    Route::get('/reviews',            [DashboardController::class, 'reviews']);
});

Route::get('/home',                   [HomeController::class, 'index']);
Route::get('/ads/{id}',               [AdController::class, 'show']);
Route::get('/marketplace/{slug}',     [MarketplaceController::class, 'show']);
Route::get('/marketplace/{slug}/ads', [MarketplaceController::class, 'ads']);
Route::get('/cities',                 [LocationController::class, 'cities']);
Route::get('/cities/{cityId}/areas',  [LocationController::class, 'areas']);
Route::get('/vendors/{id}',           [VendorController::class, 'show']);
Route::get('/plans', [PaymentController::class, 'plans']);
Route::post('/payment/paymob/callback', [PaymentController::class, 'paymobCallback']);
Route::post('/payment/fawry/callback',  [PaymentController::class, 'fawryCallback']);
Route::get('/featured-ads', [FeaturedAdsController::class, 'index']);
