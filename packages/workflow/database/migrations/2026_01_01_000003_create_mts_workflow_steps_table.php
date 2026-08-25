<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mts_workflow_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instance_id')->constrained('mts_workflow_instances')->cascadeOnDelete();
            $table->string('name');
            $table->string('type');
            $table->string('status')->index();
            $table->string('handler')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->unsignedInteger('attempts')->default(0);
            $table->unsignedInteger('max_attempts')->default(3);
            $table->unsignedInteger('timeout')->nullable();
            $table->json('result')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('next_retry_at')->nullable();
            $table->timestamps();

            $table->unique(['instance_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mts_workflow_steps');
    }
};
