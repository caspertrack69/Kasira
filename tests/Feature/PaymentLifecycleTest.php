<?php

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Jobs\SendInvoiceEmailJob;
use App\Models\Customer;
use App\Models\Entity;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Notifications\InvoiceSentNotification;
use App\Notifications\PaymentReceivedNotification;
use App\Services\Payment\PaymentAllocationService;
use App\Services\Payment\PaymentConfirmationService;
use App\Support\EntityContext;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

function paymentLifecycleFixture(array $invoiceAttributes = []): array
{
    $entity = Entity::query()->create([
        'name' => 'Kasira Entity',
        'code' => 'kasira',
        'currency' => 'IDR',
        'invoice_prefix' => 'KSR',
        'default_payment_terms' => 14,
        'default_tax_rate' => 0,
        'is_active' => true,
    ]);

    app(EntityContext::class)->setEntity($entity);

    $creator = User::factory()->create([
        'name' => 'Finance Admin',
        'email' => 'finance@example.com',
    ]);

    $customer = Customer::query()->create([
        'name' => 'Acme Corp',
        'email' => 'billing@acme.test',
        'credit_balance' => '0.00',
        'is_active' => true,
    ]);

    $paymentMethod = PaymentMethod::query()->create([
        'name' => 'Bank Transfer',
        'type' => 'bank_transfer',
        'provider' => 'manual',
        'instructions' => 'Transfer to company account.',
        'is_active' => true,
    ]);

    $invoice = Invoice::query()->create(array_merge([
        'customer_id' => $customer->getKey(),
        'invoice_number' => 'INV-001',
        'status' => InvoiceStatus::Sent->value,
        'invoice_date' => '2026-04-04',
        'due_date' => '2026-04-18',
        'subtotal' => '100.00',
        'discount_total' => '0.00',
        'tax_total' => '0.00',
        'grand_total' => '100.00',
        'amount_paid' => '0.00',
        'amount_due' => '100.00',
        'currency' => 'IDR',
        'public_token' => (string) Str::ulid(),
        'created_by' => $creator->getKey(),
    ], $invoiceAttributes));

    return compact('entity', 'creator', 'customer', 'paymentMethod', 'invoice');
}

it('keeps pending payment allocations from mutating invoice balances until confirmation', function (): void {
    $fixture = paymentLifecycleFixture();

    $payment = Payment::query()->create([
        'customer_id' => $fixture['customer']->getKey(),
        'payment_method_id' => $fixture['paymentMethod']->getKey(),
        'payment_number' => 'PAY-001',
        'amount' => '40.00',
        'payment_date' => '2026-04-04',
        'reference' => 'BANK-REF-001',
        'status' => PaymentStatus::Pending->value,
        'created_by' => $fixture['creator']->getKey(),
    ]);

    app(PaymentAllocationService::class)->allocate($payment, [
        $fixture['invoice']->getKey() => 40,
    ]);

    $fixture['invoice']->refresh();

    expect($fixture['invoice']->amount_paid)->toBe('0.00');
    expect($fixture['invoice']->amount_due)->toBe('100.00');
    expect($fixture['invoice']->status)->toBe(InvoiceStatus::Sent->value);
    expect($payment->allocations()->count())->toBe(1);
});

it('applies confirmed allocations and logs the payment notification when confirmed', function (): void {
    Notification::fake();

    $fixture = paymentLifecycleFixture();

    $payment = Payment::query()->create([
        'customer_id' => $fixture['customer']->getKey(),
        'payment_method_id' => $fixture['paymentMethod']->getKey(),
        'payment_number' => 'PAY-002',
        'amount' => '100.00',
        'payment_date' => '2026-04-04',
        'reference' => 'BANK-REF-002',
        'status' => PaymentStatus::Pending->value,
        'created_by' => $fixture['creator']->getKey(),
    ]);

    app(PaymentAllocationService::class)->allocate($payment, [
        $fixture['invoice']->getKey() => 100,
    ]);

    app(PaymentConfirmationService::class)->confirm($payment, $fixture['creator']->getKey());

    $payment->refresh();
    $fixture['invoice']->refresh();

    expect($payment->status)->toBe(PaymentStatus::Confirmed->value);
    expect($payment->confirmed_by)->toBe($fixture['creator']->getKey());
    expect($payment->confirmed_at)->not->toBeNull();
    expect($fixture['invoice']->amount_paid)->toBe('100.00');
    expect($fixture['invoice']->amount_due)->toBe('0.00');
    expect($fixture['invoice']->status)->toBe(InvoiceStatus::Paid->value);
    expect($fixture['invoice']->paid_at)->not->toBeNull();

    Notification::assertSentOnDemand(PaymentReceivedNotification::class);

    $this->assertDatabaseHas('notification_logs', [
        'entity_id' => $fixture['entity']->getKey(),
        'event_type' => 'payment_received',
        'recipient' => $fixture['customer']->email,
        'status' => 'sent',
    ]);
});

it('logs invoice email notifications when the invoice email job runs', function (): void {
    Notification::fake();

    $fixture = paymentLifecycleFixture();

    SendInvoiceEmailJob::dispatchSync($fixture['invoice']->getKey());

    Notification::assertSentOnDemand(InvoiceSentNotification::class);

    $this->assertDatabaseHas('notification_logs', [
        'entity_id' => $fixture['entity']->getKey(),
        'event_type' => 'invoice_sent',
        'recipient' => $fixture['customer']->email,
        'status' => 'sent',
    ]);
});
