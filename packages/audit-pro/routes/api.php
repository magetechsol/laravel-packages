<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use MageTech\Audit\Http\Controllers\AuditController;
use MageTech\Audit\Http\Middleware\AuditRateLimitMiddleware;
use MageTech\Audit\Http\Middleware\AuditRequestMiddleware;

Route::prefix('api')->middleware(['auth:sanctum', AuditRequestMiddleware::class, AuditRateLimitMiddleware::class])->group(function () {
    Route::get('/audits', [AuditController::class, 'index'])->name('audit.index');
    Route::get('/audits/{uuid}', [AuditController::class, 'show'])->name('audit.show');
    Route::get('/audits/{uuid}/changes', [AuditController::class, 'changes'])->name('audit.changes');
    Route::get('/auditable/{type}/{id}/audits', [AuditController::class, 'auditable'])->name('audit.auditable');
    Route::get('/actors/{id}/audits', [AuditController::class, 'actor'])->name('audit.actor');
    Route::get('/audit-stats', [AuditController::class, 'stats'])->name('audit.stats');
});
