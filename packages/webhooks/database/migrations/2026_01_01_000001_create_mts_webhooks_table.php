<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mts_webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->index();
            $table->string('event')->index();
            $table->text('signature')->nullable();
            $table->json('payload');
            $table->json('headers');
            $table->string('status')->default('pending')->index();
            $table->unsignedInteger('attempts')->default(0);
            $table->unsignedInteger('max_attempts')->default(3);
            $table->string('idempotency_key')->nullable()->unique();
            $table->string('request_id')->nullable()->index();
            $table->string('source_ip')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('dead_at')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('next_retry_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mts_webhooks');
    }
};
