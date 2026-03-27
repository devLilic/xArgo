<?php

use App\Http\Controllers\Api\V1\LicenseActivationController;
use App\Http\Controllers\Api\V1\LicenseRebindController;
use App\Http\Controllers\Api\V1\LicenseValidationController;
use App\Support\Api\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('licenses')
    ->as('licenses.')
    ->middleware('throttle:licensing-api')
    ->group(function (): void {
        Route::post('/activate', [LicenseActivationController::class, 'store'])
            ->name('activate');

        Route::post('/validate', [LicenseValidationController::class, 'store'])
            ->name('validate');

        Route::post('/heartbeat', [LicenseActivationController::class, 'heartbeat'])
            ->name('heartbeat');

        Route::post('/rebind/request', [LicenseRebindController::class, 'request'])
            ->name('rebind.request');

        Route::post('/rebind/confirm', [LicenseRebindController::class, 'confirm'])
            ->name('rebind.confirm');

        Route::get('/ping', function (Request $request) {
            return ApiResponse::success([
                'ok' => true,
                'app' => config('app.name'),
                'timestamp' => now()->toIso8601String(),
            ]);
        })->name('ping');
    });
