<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('entity_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUlid('payment_method_id')->constrained()->restrictOnDelete();
            $table->string('payment_number', 50);
            $table->decimal('amount', 15, 2);
            $table->date('payment_date');
            $table->string('reference', 150)->nullable();
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'reversed'])->default('pending');
            $table->text('notes')->nullable();
            $table->string('proof_path')->nullable();
            $table->json('gateway_response')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['entity_id', 'status']);
            $table->index(['entity_id', 'payment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};