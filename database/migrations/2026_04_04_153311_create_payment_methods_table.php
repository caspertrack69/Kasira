<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('entity_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->enum('type', ['bank_transfer', 'cash', 'virtual_account', 'qris', 'cheque', 'other'])->default('bank_transfer');
            $table->string('account_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('provider')->nullable();
            $table->text('instructions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['entity_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};