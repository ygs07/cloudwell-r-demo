<?php

use App\Http\Controllers\Api\v1\ReferralController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('/v1')->group(function () {
    Route::post('/referrals', [ReferralController::class, 'store'])
        ->name('api.v1.referrals.store')
        ->middleware('auth:sanctum');

    Route::get('/referrals', [ReferralController::class, 'index'])
        ->name('api.v1.referrals.index')
        ->middleware('auth:sanctum');

    Route::get('/referrals/{referral}', [ReferralController::class, 'show'])
        ->name('api.v1.referrals.show')
        ->middleware('auth:sanctum');
});
