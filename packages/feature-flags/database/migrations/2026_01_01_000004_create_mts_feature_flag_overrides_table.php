<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mts_feature_flag_overrides', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feature_flag_id');
            $table->string('subject_type');
            $table->unsignedBigInteger('subject_id');
            $table->boolean('enabled')->default(true);
            $table->string('variant')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->foreign('feature_flag_id')
                ->references('id')
                ->on('mts_feature_flags')
                ->cascadeOnDelete();

            $table->unique(['feature_flag_id', 'subject_type', 'subject_id']);
            $table->index('subject_type');
            $table->index('subject_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mts_feature_flag_overrides');
    }
};
