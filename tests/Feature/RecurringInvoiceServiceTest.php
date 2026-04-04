<?php

use App\Enums\InvoiceStatus;
use App\Jobs\GenerateInvoicePdfJob;
use App\Jobs\SendInvoiceEmailJob;
use App\Models\Customer;
use App\Models\Entity;
use App\Models\Invoice;
use App\Models\RecurringTemplate;
use App\Models\User;
use App\Services\Billing\RecurringInvoiceService;
use App\Support\EntityContext;
use Illuminate\Support\Facades\Queue;

it('generates invoices for due recurring templates and advances the schedule', function (): void {
    $entity = Entity::query()->create([
        'name' => 'Kasira Recurring',
        'code' => 'KSR-REC',
        'currency' => 'IDR',
        'invoice_prefix' => 'KREC',
        'default_payment_terms' => 30,
        'default_tax_rate' => 0,
        'is_active' => true,
    ]);

    app(EntityContext::class)->setEntity($entity);

    $user = User::factory()->create();

    $customer = Customer::query()->create([
        'name' => 'Recurring Customer',
        'email' => 'recurring@example.test',
        'credit_balance' => '0.00',
        'is_active' => true,
    ]);

    $template = RecurringTemplate::query()->create([
        'customer_id' => $customer->getKey(),
        'name' => 'Monthly Support Plan',
        'frequency' => 'monthly',
        'start_date' => now()->subMonth()->toDateString(),
        'end_date' => null,
        'occurrences_limit' => 3,
        'occurrences_count' => 0,
        'next_generate_date' => now()->toDateString(),
        'auto_send' => false,
        'is_active' => true,
        'template_data' => [
            'subtotal' => '250000.00',
            'discount_total' => '0.00',
            'tax_total' => '0.00',
            'grand_total' => '250000.00',
            'currency' => 'IDR',
            'items' => [
                [
                    'description' => 'Monthly support retainer',
                    'quantity' => 1,
                    'unit_price' => '250000.00',
                    'discount_amount' => '0.00',
                    'tax_rate' => '0.00',
                    'tax_amount' => '0.00',
                    'subtotal' => '250000.00',
                ],
            ],
        ],
        'created_by' => $user->getKey(),
    ]);

    $generated = app(RecurringInvoiceService::class)->generateDueTemplates(now());

    $template->refresh();
    $invoice = Invoice::query()->withoutGlobalScopes()->where('recurring_template_id', $template->getKey())->first();

    expect($generated)->toBe(1);
    expect($invoice)->not->toBeNull();
    expect($invoice->status)->toBe(InvoiceStatus::Draft->value);
    expect($invoice->amount_due)->toBe('250000.00');
    expect($template->occurrences_count)->toBe(1);
    expect($template->next_generate_date->toDateString())->toBe(now()->addMonth()->toDateString());
    expect($template->is_active)->toBeTrue();

    $this->assertDatabaseHas('invoice_status_histories', [
        'invoice_id' => $invoice->getKey(),
        'to_status' => InvoiceStatus::Draft->value,
    ]);
});

it('queues delivery jobs when a recurring template is configured for auto send', function (): void {
    Queue::fake();

    $entity = Entity::query()->create([
        'name' => 'Kasira Auto Send',
        'code' => 'KSR-AUTO',
        'currency' => 'IDR',
        'invoice_prefix' => 'KAUTO',
        'default_payment_terms' => 14,
        'default_tax_rate' => 0,
        'is_active' => true,
    ]);

    app(EntityContext::class)->setEntity($entity);

    $user = User::factory()->create();
    $customer = Customer::query()->create([
        'name' => 'Auto Send Customer',
        'email' => 'autosend@example.test',
        'credit_balance' => '0.00',
        'is_active' => true,
    ]);

    $template = RecurringTemplate::query()->create([
        'customer_id' => $customer->getKey(),
        'name' => 'Auto Send Plan',
        'frequency' => 'monthly',
        'start_date' => now()->subMonth()->toDateString(),
        'end_date' => null,
        'occurrences_limit' => 2,
        'occurrences_count' => 0,
        'next_generate_date' => now()->toDateString(),
        'auto_send' => true,
        'is_active' => true,
        'template_data' => [
            'subtotal' => '500000.00',
            'discount_total' => '0.00',
            'tax_total' => '0.00',
            'grand_total' => '500000.00',
            'currency' => 'IDR',
            'items' => [
                [
                    'description' => 'Auto send service',
                    'quantity' => 1,
                    'unit_price' => '500000.00',
                    'discount_amount' => '0.00',
                    'tax_rate' => '0.00',
                    'tax_amount' => '0.00',
                    'subtotal' => '500000.00',
                ],
            ],
        ],
        'created_by' => $user->getKey(),
    ]);

    app(RecurringInvoiceService::class)->generateDueTemplates(now());

    $invoice = Invoice::query()->withoutGlobalScopes()->where('recurring_template_id', $template->getKey())->firstOrFail();

    expect($invoice->status)->toBe(InvoiceStatus::Sent->value);
    expect($invoice->sent_at)->not->toBeNull();

    Queue::assertPushed(GenerateInvoicePdfJob::class, fn (GenerateInvoicePdfJob $job): bool => $job->invoiceId === $invoice->getKey());
    Queue::assertPushed(SendInvoiceEmailJob::class, fn (SendInvoiceEmailJob $job): bool => $job->invoiceId === $invoice->getKey());

    $this->assertDatabaseHas('invoice_status_histories', [
        'invoice_id' => $invoice->getKey(),
        'to_status' => InvoiceStatus::Sent->value,
    ]);
});
