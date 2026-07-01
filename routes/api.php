<?php

use App\Http\Controllers\Api\AuthTokenController;
use App\Http\Controllers\Api\ChromeExtensionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/auth/token', [AuthTokenController::class, 'store'])
        ->name('api.v1.auth.token');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/jobs/import', [ChromeExtensionController::class, 'store'])
            ->name('api.v1.jobs.import');

        Route::delete('/auth/token', [AuthTokenController::class, 'destroy'])
            ->name('api.v1.auth.revoke');
    });
});
