<?php

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Models\Customer;
use App\Models\Entity;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Services\Report\FinancialReportService;
use App\Support\EntityContext;
use Illuminate\Support\Str;

it('summarizes only operational invoices and confirmed payments for an entity', function (): void {
    $entity = Entity::query()->create([
        'name' => 'Kasira Reports',
        'code' => 'KSR-RPT',
        'currency' => 'IDR',
        'invoice_prefix' => 'KRPT',
        'default_payment_terms' => 14,
        'default_tax_rate' => 0,
        'is_active' => true,
    ]);

    app(EntityContext::class)->setEntity($entity);

    $user = User::factory()->create();

    $customer = Customer::query()->create([
        'name' => 'Reporting Customer',
        'email' => 'reports@example.test',
        'credit_balance' => '0.00',
        'is_active' => true,
    ]);

    $paymentMethod = PaymentMethod::query()->create([
        'name' => 'Bank Transfer',
        'type' => 'bank_transfer',
        'provider' => 'manual',
        'instructions' => 'Transfer manually.',
        'is_active' => true,
    ]);

    $baseAttributes = [
        'customer_id' => $customer->getKey(),
        'invoice_date' => now()->toDateString(),
        'currency' => 'IDR',
        'notes' => null,
        'terms' => null,
        'created_by' => $user->getKey(),
    ];

    Invoice::query()->create($baseAttributes + [
        'invoice_number' => 'KRPT-2026-0001',
        'status' => InvoiceStatus::Partial->value,
        'due_date' => now()->subDays(10)->toDateString(),
        'subtotal' => '100.00',
        'discount_total' => '0.00',
        'tax_total' => '0.00',
        'grand_total' => '100.00',
        'amount_paid' => '60.00',
        'amount_due' => '40.00',
        'public_token' => (string) Str::ulid(),
    ]);

    Invoice::query()->create($baseAttributes + [
        'invoice_number' => 'KRPT-2026-0002',
        'status' => InvoiceStatus::Paid->value,
        'due_date' => now()->subDays(2)->toDateString(),
        'subtotal' => '50.00',
        'discount_total' => '0.00',
        'tax_total' => '0.00',
        'grand_total' => '50.00',
        'amount_paid' => '50.00',
        'amount_due' => '0.00',
        'paid_at' => now(),
        'public_token' => (string) Str::ulid(),
    ]);

    Invoice::query()->create($baseAttributes + [
        'invoice_number' => 'KRPT-2026-0003',
        'status' => InvoiceStatus::Overdue->value,
        'due_date' => now()->subDays(70)->toDateString(),
        'subtotal' => '20.00',
        'discount_total' => '0.00',
        'tax_total' => '0.00',
        'grand_total' => '20.00',
        'amount_paid' => '0.00',
        'amount_due' => '20.00',
        'public_token' => (string) Str::ulid(),
    ]);

    Invoice::query()->create($baseAttributes + [
        'invoice_number' => 'KRPT-2026-0004',
        'status' => InvoiceStatus::Draft->value,
        'due_date' => now()->addDays(7)->toDateString(),
        'subtotal' => '999.00',
        'discount_total' => '0.00',
        'tax_total' => '0.00',
        'grand_total' => '999.00',
        'amount_paid' => '0.00',
        'amount_due' => '999.00',
        'public_token' => (string) Str::ulid(),
    ]);

    Payment::query()->create([
        'customer_id' => $customer->getKey(),
        'payment_method_id' => $paymentMethod->getKey(),
        'payment_number' => 'PAY-REPORT-1',
        'amount' => '60.00',
        'payment_date' => now()->subDay()->toDateString(),
        'status' => PaymentStatus::Confirmed->value,
        'created_by' => $user->getKey(),
        'confirmed_by' => $user->getKey(),
        'confirmed_at' => now(),
    ]);

    Payment::query()->create([
        'customer_id' => $customer->getKey(),
        'payment_method_id' => $paymentMethod->getKey(),
        'payment_number' => 'PAY-REPORT-2',
        'amount' => '25.00',
        'payment_date' => now()->toDateString(),
        'status' => PaymentStatus::Pending->value,
        'created_by' => $user->getKey(),
    ]);

    $summary = app(FinancialReportService::class)->summary(
        $entity->getKey(),
        now()->startOfMonth()->toDateString(),
        now()->endOfMonth()->toDateString(),
    );

    expect($summary['invoice_total'])->toBe('170.00');
    expect($summary['payment_total'])->toBe('60.00');
    expect($summary['outstanding_total'])->toBe('60.00');
    expect($summary['aging'])->toBe([
        'current' => '0.00',
        '30' => '40.00',
        '60' => '0.00',
        '90_plus' => '20.00',
    ]);
});
