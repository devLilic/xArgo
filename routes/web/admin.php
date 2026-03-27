<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserInvitationController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');

Route::post('/invitations', [UserInvitationController::class, 'store'])
    ->name('invitations.store');

Route::get('/users', [UserController::class, 'index'])
    ->name('users.index');

Route::get('/users/{user}', [UserController::class, 'show'])
    ->name('users.show');

Route::patch('/users/{user}/role', [UserController::class, 'updateRole'])
    ->name('users.role.update');

Route::patch('/users/{user}/activity', [UserController::class, 'updateActivity'])
    ->name('users.activity.update');
