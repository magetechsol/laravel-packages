<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use MageTech\DevTools\Http\DashboardController;

Route::get('/', DashboardController::class)->name('dashboard');

Route::post('/logout', function () {
    session()->forget('devtools_authenticated');

    return redirect()->route('devtools.dashboard');
})->name('logout');
