<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_status_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignUlid('entity_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['invoice_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_status_histories');
    }
};