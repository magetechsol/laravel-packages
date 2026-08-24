<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use MageTech\Webhooks\Http\Controllers\WebhookController;

Route::post('/{provider}', [WebhookController::class, 'handle'])
    ->name('webhooks.receive');
