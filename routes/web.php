<?php

use Illuminate\Support\Facades\Route;

Route::middleware('web')
    ->group(__DIR__.'/web/auth.php');

Route::middleware('auth')
    ->as('admin.')
    ->group(__DIR__.'/web/admin.php');
