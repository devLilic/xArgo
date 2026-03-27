<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('licenses')
    ->as('licenses.')
    ->group(function (): void {
        Route::get('/ping', function (Request $request) {
            return response()->json([
                'ok' => true,
                'app' => config('app.name'),
                'timestamp' => now()->toIso8601String(),
            ]);
        })->name('ping');
    });
