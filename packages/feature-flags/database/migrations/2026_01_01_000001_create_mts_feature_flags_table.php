<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mts_feature_flags', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('key');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type')->default('boolean');
            $table->boolean('enabled')->default(false);
            $table->string('environment')->nullable();
            $table->unsignedInteger('rollout_percentage')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->string('default_variant')->nullable();
            $table->json('metadata')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['environment', 'key']);
            $table->index('type');
            $table->index('enabled');
            $table->index('environment');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mts_feature_flags');
    }
};
