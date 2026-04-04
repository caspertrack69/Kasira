<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_events', function (Blueprint $table): void {
            $table->id();
            $table->ulid('entity_id')->nullable();
            $table->enum('gateway', ['midtrans', 'xendit']);
            $table->string('event_id', 150)->nullable();
            $table->string('signature', 255)->nullable();
            $table->json('payload');
            $table->enum('status', ['received', 'processed', 'failed'])->default('received');
            $table->timestamp('processed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['gateway', 'event_id']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_events');
    }
};