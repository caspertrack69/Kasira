<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('entity_id')->constrained()->cascadeOnDelete();
            $table->string('sku', 60)->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('unit', 50)->default('unit');
            $table->decimal('default_price', 15, 2)->default(0);
            $table->boolean('is_taxable')->default(true);
            $table->foreignUlid('tax_id')->nullable()->constrained('taxes')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['entity_id', 'sku']);
            $table->index(['entity_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};