<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurring_templates', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('entity_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('customer_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'quarterly', 'annually']);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->unsignedSmallInteger('occurrences_limit')->nullable();
            $table->unsignedSmallInteger('occurrences_count')->default(0);
            $table->date('next_generate_date');
            $table->boolean('auto_send')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('template_data');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['next_generate_date', 'is_active']);
        });

        Schema::table('invoices', function (Blueprint $table): void {
            $table->foreign('recurring_template_id')->references('id')->on('recurring_templates')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropForeign(['recurring_template_id']);
        });

        Schema::dropIfExists('recurring_templates');
    }
};