<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('entity_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('customer_id')->constrained()->restrictOnDelete();
            $table->ulid('recurring_template_id')->nullable();
            $table->string('invoice_number', 50);
            $table->enum('status', ['draft', 'sent', 'partial', 'paid', 'overdue', 'cancelled', 'void'])->default('draft');
            $table->date('invoice_date');
            $table->date('due_date');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_total', 15, 2)->default(0);
            $table->decimal('tax_total', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->decimal('amount_due', 15, 2)->default(0);
            $table->char('currency', 3)->default('IDR');
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            $table->string('pdf_path')->nullable();
            $table->string('public_token', 80)->unique();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['entity_id', 'invoice_number']);
            $table->index(['entity_id', 'status']);
            $table->index(['entity_id', 'due_date', 'status']);
            $table->index(['entity_id', 'customer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};