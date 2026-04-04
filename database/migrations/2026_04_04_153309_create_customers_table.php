<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('entity_id')->constrained()->cascadeOnDelete();
            $table->string('customer_number', 40)->nullable();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('tax_id', 60)->nullable();
            $table->text('billing_address')->nullable();
            $table->text('shipping_address')->nullable();
            $table->decimal('credit_balance', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['entity_id', 'customer_number']);
            $table->index(['entity_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};