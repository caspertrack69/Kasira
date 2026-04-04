<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entities', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('code', 20)->unique();
            $table->string('legal_name')->nullable();
            $table->string('tax_id', 60)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 120)->nullable();
            $table->string('province', 120)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('country', 2)->default('ID');
            $table->string('phone', 40)->nullable();
            $table->string('email')->nullable();
            $table->string('logo_path')->nullable();
            $table->char('currency', 3)->default('IDR');
            $table->string('invoice_prefix', 12)->default('ENT');
            $table->unsignedSmallInteger('default_payment_terms')->default(30);
            $table->decimal('default_tax_rate', 5, 2)->default(0);
            $table->json('reminder_days')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entities');
    }
};