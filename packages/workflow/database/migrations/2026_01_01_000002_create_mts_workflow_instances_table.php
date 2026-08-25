<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mts_workflow_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained('mts_workflows')->cascadeOnDelete();
            $table->string('workflowable_type');
            $table->unsignedBigInteger('workflowable_id');
            $table->string('current_step')->nullable();
            $table->string('status')->index();
            $table->json('context')->nullable();
            $table->unsignedBigInteger('started_by')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('error')->nullable();
            $table->string('request_id')->nullable()->index();
            $table->timestamps();

            $table->index(['workflowable_type', 'workflowable_id']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mts_workflow_instances');
    }
};
