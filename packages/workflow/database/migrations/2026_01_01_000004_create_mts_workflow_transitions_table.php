<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mts_workflow_transitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instance_id')->constrained('mts_workflow_instances')->cascadeOnDelete();
            $table->string('step_name')->nullable();
            $table->string('type');
            $table->string('from_state');
            $table->string('to_state');
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('actor_type')->nullable();
            $table->text('reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['instance_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mts_workflow_transitions');
    }
};
