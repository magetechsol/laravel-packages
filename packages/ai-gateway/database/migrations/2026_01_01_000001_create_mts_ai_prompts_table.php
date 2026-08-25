<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mts_ai_prompts', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->unsignedInteger('version')->default(1);
            $table->text('template');
            $table->json('variables')->nullable();
            $table->string('model')->nullable();
            $table->float('temperature')->nullable();
            $table->unsignedInteger('max_tokens')->nullable();
            $table->string('status')->default('active');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['name', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mts_ai_prompts');
    }
};
