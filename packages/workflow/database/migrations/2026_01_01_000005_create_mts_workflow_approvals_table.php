<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mts_workflow_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instance_id')->constrained('mts_workflow_instances')->cascadeOnDelete();
            $table->string('step_name');
            $table->string('approval_type');
            $table->unsignedBigInteger('approver_id')->nullable();
            $table->string('approver_type')->nullable();
            $table->string('status')->index();
            $table->string('decision')->nullable();
            $table->text('comment')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['instance_id', 'step_name', 'approver_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mts_workflow_approvals');
    }
};
