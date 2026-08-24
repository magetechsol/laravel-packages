<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('row_number');
            $table->json('data');
            $table->json('mapped_data')->nullable();
            $table->string('status')->default('pending');
            $table->text('error_message')->nullable();
            $table->json('error_details')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['import_id', 'status']);
            $table->index(['import_id', 'row_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_rows');
    }
};
