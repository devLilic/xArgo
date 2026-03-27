<?php

use Illuminate\Support\Facades\Route;

Route::middleware('web')
    ->as('admin.')
    ->group(__DIR__.'/web/admin.php');
