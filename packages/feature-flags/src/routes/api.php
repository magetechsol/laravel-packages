<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use MageTech\FeatureFlags\Http\Controllers\FeatureFlagController;

Route::prefix('feature-flags')->group(function () {
    Route::get('/', [FeatureFlagController::class, 'index'])
        ->name('feature-flags.index');

    Route::post('/', [FeatureFlagController::class, 'store'])
        ->name('feature-flags.store');

    Route::get('/{key}', [FeatureFlagController::class, 'show'])
        ->name('feature-flags.show');

    Route::put('/{key}', [FeatureFlagController::class, 'update'])
        ->name('feature-flags.update');

    Route::delete('/{key}', [FeatureFlagController::class, 'destroy'])
        ->name('feature-flags.destroy');

    Route::post('/{key}/enable', [FeatureFlagController::class, 'enable'])
        ->name('feature-flags.enable');

    Route::post('/{key}/disable', [FeatureFlagController::class, 'disable'])
        ->name('feature-flags.disable');

    Route::post('/{key}/evaluate', [FeatureFlagController::class, 'evaluate'])
        ->name('feature-flags.evaluate');
});
