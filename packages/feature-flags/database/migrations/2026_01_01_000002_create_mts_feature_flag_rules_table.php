<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mts_feature_flag_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feature_flag_id');
            $table->string('rule_type');
            $table->string('operator')->default('equals');
            $table->string('attribute');
            $table->string('value');
            $table->unsignedInteger('priority')->default(0);
            $table->boolean('enabled')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('feature_flag_id')
                ->references('id')
                ->on('mts_feature_flags')
                ->cascadeOnDelete();

            $table->index('feature_flag_id');
            $table->index('rule_type');
            $table->index('priority');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mts_feature_flag_rules');
    }
};
