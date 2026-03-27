<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->as('api.v1.')
    ->group(__DIR__.'/api/v1/licensing.php');
