<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entity_users', function (Blueprint $table): void {
            $table->id();
            $table->foreignUlid('entity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 50);
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['entity_id', 'user_id']);
            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entity_users');
    }
};