<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_notes', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('entity_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('invoice_id')->constrained()->restrictOnDelete();
            $table->foreignUlid('customer_id')->constrained()->restrictOnDelete();
            $table->string('credit_note_number', 50);
            $table->decimal('amount', 15, 2);
            $table->text('reason');
            $table->enum('status', ['issued', 'applied', 'void'])->default('issued');
            $table->string('pdf_path')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['entity_id', 'credit_note_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_notes');
    }
};