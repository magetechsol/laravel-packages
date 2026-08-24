<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mts_webhook_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('webhook_id')->constrained('mts_webhooks')->cascadeOnDelete();
            $table->unsignedInteger('attempt_number');
            $table->string('status');
            $table->json('payload')->nullable();
            $table->text('error')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->timestamps();

            $table->index(['webhook_id', 'attempt_number']);
            $table->index(['webhook_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mts_webhook_attempts');
    }
};
