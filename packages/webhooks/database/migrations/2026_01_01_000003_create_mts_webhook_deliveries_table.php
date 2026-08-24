<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mts_webhook_deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('event_name')->index();
            $table->string('url');
            $table->json('payload');
            $table->json('headers')->nullable();
            $table->string('status')->default('pending')->index();
            $table->unsignedInteger('attempts')->default(0);
            $table->unsignedInteger('max_attempts')->default(5);
            $table->unsignedInteger('response_code')->nullable();
            $table->text('response_body')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('next_retry_at')->nullable()->index();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('dead_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'next_retry_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mts_webhook_deliveries');
    }
};
