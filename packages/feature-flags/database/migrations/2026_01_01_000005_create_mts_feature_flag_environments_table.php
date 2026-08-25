<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mts_feature_flag_environments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feature_flag_id');
            $table->string('environment');
            $table->boolean('enabled')->default(false);
            $table->unsignedInteger('rollout_percentage')->default(0);
            $table->json('configuration')->nullable();
            $table->timestamps();

            $table->foreign('feature_flag_id')
                ->references('id')
                ->on('mts_feature_flags')
                ->cascadeOnDelete();

            $table->unique(['feature_flag_id', 'environment']);
            $table->index('environment');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mts_feature_flag_environments');
    }
};
