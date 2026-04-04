<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table): void {
            $table->id();
            $table->ulid('entity_id')->nullable();
            $table->string('notifiable_type', 100)->nullable();
            $table->string('notifiable_id', 36)->nullable();
            $table->enum('channel', ['email', 'sms'])->default('email');
            $table->string('event_type', 100);
            $table->string('recipient', 150);
            $table->enum('status', ['sent', 'failed', 'pending'])->default('pending');
            $table->text('failed_reason')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['entity_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};