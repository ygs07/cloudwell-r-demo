<?php

use App\Http\Controllers\Api\v1\ReferralController;
use App\Http\Middleware\EnsureIdempotency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('/v1')->group(function () {
    Route::get('/referrals', [ReferralController::class, 'index'])
        ->name('api.v1.referrals.index')
        ->middleware('auth:sanctum');
    Route::post('/referrals', [ReferralController::class, 'store'])
        ->name('api.v1.referrals.store')
        ->middleware('auth:sanctum', 'throttle:referrals','idempotent');

    Route::get('/referrals/{referral}', [ReferralController::class, 'show'])
        ->name('api.v1.referrals.show')
        ->middleware('auth:sanctum');

    Route::patch('/referrals/{referral}/cancel', [ReferralController::class, 'cancel'])
        ->name('api.v1.referrals.cancel')
        ->middleware('auth:sanctum', 'throttle:referrals');
});
