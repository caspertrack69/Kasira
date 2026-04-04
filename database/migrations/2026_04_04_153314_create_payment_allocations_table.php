<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_allocations', function (Blueprint $table): void {
            $table->id();
            $table->foreignUlid('payment_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('invoice_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->timestamps();

            $table->unique(['payment_id', 'invoice_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_allocations');
    }
};