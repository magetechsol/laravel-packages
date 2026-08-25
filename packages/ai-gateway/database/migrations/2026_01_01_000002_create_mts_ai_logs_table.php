<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mts_ai_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('request_id')->index();
            $table->uuid('correlation_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->string('prompt_name')->nullable()->index();
            $table->unsignedInteger('prompt_version')->nullable();
            $table->string('provider')->index();
            $table->string('model')->index();
            $table->unsignedInteger('input_tokens')->default(0);
            $table->unsignedInteger('output_tokens')->default(0);
            $table->unsignedInteger('total_tokens')->default(0);
            $table->decimal('estimated_cost', 12, 6)->default(0);
            $table->float('duration_ms')->default(0);
            $table->string('status')->index();
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['tenant_id', 'created_at']);
            $table->index(['provider', 'model']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mts_ai_logs');
    }
};
