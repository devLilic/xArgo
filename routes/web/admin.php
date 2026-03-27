<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserInvitationController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');

Route::post('/invitations', [UserInvitationController::class, 'store'])
    ->name('invitations.store');
