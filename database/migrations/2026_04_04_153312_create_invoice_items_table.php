<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('item_id')->nullable()->constrained('items')->nullOnDelete();
            $table->string('description');
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 15, 2);
            $table->enum('discount_type', ['percentage', 'fixed'])->nullable();
            $table->decimal('discount_value', 15, 2)->nullable();
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->foreignUlid('tax_id')->nullable()->constrained('taxes')->nullOnDelete();
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2);
            $table->smallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};