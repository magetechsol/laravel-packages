<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mts_workflow_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instance_id')->constrained('mts_workflow_instances')->cascadeOnDelete();
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('actor_type')->nullable();
            $table->string('action');
            $table->string('from_state')->nullable();
            $table->string('to_state')->nullable();
            $table->string('step_name')->nullable();
            $table->text('reason')->nullable();
            $table->json('metadata')->nullable();
            $table->string('request_id')->nullable()->index();
            $table->string('ip_address')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['instance_id', 'action']);
            $table->index(['instance_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mts_workflow_logs');
    }
};
