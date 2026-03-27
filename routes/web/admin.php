<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AppController;
use App\Http\Controllers\Admin\LicenseController;
use App\Http\Controllers\Admin\LicensePlanController;
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

Route::get('/apps', [AppController::class, 'index'])
    ->name('apps.index');

Route::post('/apps', [AppController::class, 'store'])
    ->name('apps.store');

Route::get('/apps/{app}/edit', [AppController::class, 'edit'])
    ->name('apps.edit');

Route::patch('/apps/{app}', [AppController::class, 'update'])
    ->name('apps.update');

Route::get('/plans', [LicensePlanController::class, 'index'])
    ->name('plans.index');

Route::post('/plans', [LicensePlanController::class, 'store'])
    ->name('plans.store');

Route::get('/plans/{plan}/edit', [LicensePlanController::class, 'edit'])
    ->name('plans.edit');

Route::patch('/plans/{plan}', [LicensePlanController::class, 'update'])
    ->name('plans.update');

Route::get('/licenses', [LicenseController::class, 'index'])
    ->name('licenses.index');

Route::get('/licenses/export', [LicenseController::class, 'export'])
    ->name('licenses.export');

Route::post('/licenses', [LicenseController::class, 'store'])
    ->name('licenses.store');

Route::get('/licenses/{license}', [LicenseController::class, 'show'])
    ->name('licenses.show');

Route::get('/licenses/{license}/edit', [LicenseController::class, 'edit'])
    ->name('licenses.edit');

Route::patch('/licenses/{license}', [LicenseController::class, 'update'])
    ->name('licenses.update');

Route::patch('/licenses/{license}/status', [LicenseController::class, 'updateStatus'])
    ->name('licenses.status.update');

Route::delete('/licenses/{license}', [LicenseController::class, 'destroy'])
    ->name('licenses.destroy');

Route::patch('/licenses/{license}/restore', [LicenseController::class, 'restore'])
    ->name('licenses.restore');
