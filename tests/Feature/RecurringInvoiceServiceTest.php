<?php

use App\Enums\InvoiceStatus;
use App\Models\Customer;
use App\Models\Entity;
use App\Models\Invoice;
use App\Models\RecurringTemplate;
use App\Models\User;
use App\Services\Billing\RecurringInvoiceService;
use App\Support\EntityContext;

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
});
