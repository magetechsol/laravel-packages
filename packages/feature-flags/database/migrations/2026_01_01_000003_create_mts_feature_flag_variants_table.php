<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mts_feature_flag_variants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feature_flag_id');
            $table->string('key');
            $table->string('name');
            $table->json('value')->nullable();
            $table->unsignedInteger('weight')->default(1);
            $table->boolean('enabled')->default(true);
            $table->timestamps();

            $table->foreign('feature_flag_id')
                ->references('id')
                ->on('mts_feature_flags')
                ->cascadeOnDelete();

            $table->unique(['feature_flag_id', 'key']);
            $table->index('feature_flag_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mts_feature_flag_variants');
    }
};
