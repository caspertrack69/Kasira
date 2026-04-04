<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taxes', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('entity_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 40)->nullable();
            $table->enum('type', ['inclusive', 'exclusive'])->default('exclusive');
            $table->decimal('rate', 5, 2);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['entity_id', 'name']);
            $table->index(['entity_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taxes');
    }
};