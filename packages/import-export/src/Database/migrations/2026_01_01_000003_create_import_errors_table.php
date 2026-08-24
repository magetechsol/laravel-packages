<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_errors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_id')->constrained()->cascadeOnDelete();
            $table->foreignId('import_row_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('row_number');
            $table->string('column')->nullable();
            $table->text('value')->nullable();
            $table->text('error');
            $table->string('error_code')->nullable();
            $table->json('context')->nullable();
            $table->timestamps();

            $table->index(['import_id', 'row_number']);
            $table->index('error_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_errors');
    }
};
