<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection(config('audit.connection'))->create(config('audit.table', 'audits'), function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('event', 50)->index();
            $table->string('auditable_type')->nullable()->index();
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->string('actor_type')->nullable();
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('actor_name')->nullable();
            $table->string('actor_email')->nullable();
            $table->string('action')->nullable();
            $table->text('description')->nullable();
            $table->string('url')->nullable();
            $table->string('method', 10)->nullable();
            $table->string('route')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('request_id')->nullable()->index();
            $table->string('session_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->json('changed_values')->nullable();
            $table->json('metadata')->nullable();
            $table->json('tags')->nullable();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->uuid('batch_uuid')->nullable()->index();
            $table->string('previous_hash')->nullable();
            $table->string('record_hash')->nullable();
            $table->timestamp('created_at')->nullable()->index();

            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['actor_type', 'actor_id']);
        });
    }

    public function down(): void
    {
        Schema::connection(config('audit.connection'))->dropIfExists(config('audit.table', 'audits'));
    }
};
