<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->get('/v1/ping', function (Request $request) {
    return response()->json([
        'ok' => true,
        'app' => config('app.name'),
        'timestamp' => now()->toIso8601String(),
    ]);
})->name('api.v1.ping');
