<?php

use App\Models\Customer;
use App\Models\Entity;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Models\WebhookEvent;
use App\Notifications\PaymentReceivedNotification;
use App\Services\Payment\PaymentAllocationService;
use App\Support\EntityContext;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

function webhookFixture(string $provider = 'Midtrans'): array
{
    $entity = Entity::query()->create([
        'name' => 'Webhook Entity',
        'code' => 'WH-ENT',
        'currency' => 'IDR',
        'invoice_prefix' => 'WHE',
        'default_payment_terms' => 14,
        'default_tax_rate' => 0,
        'is_active' => true,
    ]);

    app(EntityContext::class)->setEntity($entity);

    $creator = User::factory()->create();
    $customer = Customer::query()->create([
        'name' => 'Webhook Customer',
        'email' => 'webhook@example.test',
        'credit_balance' => '0.00',
        'is_active' => true,
    ]);

    $paymentMethod = PaymentMethod::query()->create([
        'name' => 'Gateway QRIS',
        'type' => 'qris',
        'provider' => $provider,
        'instructions' => 'Gateway payment',
        'is_active' => true,
    ]);

    $invoice = Invoice::query()->create([
        'customer_id' => $customer->getKey(),
        'invoice_number' => 'WHE-001',
        'status' => 'sent',
        'invoice_date' => now()->toDateString(),
        'due_date' => now()->addDays(14)->toDateString(),
        'subtotal' => '100.00',
        'discount_total' => '0.00',
        'tax_total' => '0.00',
        'grand_total' => '100.00',
        'amount_paid' => '0.00',
        'amount_due' => '100.00',
        'currency' => 'IDR',
        'public_token' => (string) Str::ulid(),
        'created_by' => $creator->getKey(),
    ]);

    $payment = Payment::query()->create([
        'customer_id' => $customer->getKey(),
        'payment_method_id' => $paymentMethod->getKey(),
        'payment_number' => 'PAY-WEBHOOK-001',
        'amount' => '100.00',
        'payment_date' => now()->toDateString(),
        'reference' => 'WHE-001-QR-REF',
        'status' => 'pending',
        'gateway_response' => [
            'gateway' => str_contains(strtolower($provider), 'xendit') ? 'xendit' : 'midtrans',
            'reference' => 'WHE-001-QR-REF',
        ],
        'created_by' => $creator->getKey(),
    ]);

    app(PaymentAllocationService::class)->allocate($payment, [
        $invoice->getKey() => 100,
    ]);

    return compact('entity', 'creator', 'customer', 'paymentMethod', 'invoice', 'payment');
}

it('processes midtrans webhooks idempotently', function (): void {
    Notification::fake();

    config()->set('services.midtrans.server_key', 'midtrans-server-key');

    $fixture = webhookFixture('Midtrans');

    $payload = [
        'transaction_id' => 'midtrans-tx-001',
        'transaction_status' => 'settlement',
        'order_id' => $fixture['payment']->reference,
        'gross_amount' => '100.00',
        'status_code' => '200',
    ];
    $payload['signature_key'] = hash('sha512', $payload['order_id'].$payload['status_code'].$payload['gross_amount'].'midtrans-server-key');

    $firstResponse = $this->postJson(route('webhooks.midtrans'), $payload);
    $secondResponse = $this->postJson(route('webhooks.midtrans'), $payload);

    $firstResponse->assertOk();
    $secondResponse->assertOk();

    $fixture['payment']->refresh();
    $fixture['invoice']->refresh();

    expect($fixture['payment']->status)->toBe('confirmed');
    expect($fixture['invoice']->status)->toBe('paid');
    expect(WebhookEvent::query()->count())->toBe(1);
    expect(WebhookEvent::query()->firstOrFail()->status)->toBe('processed');

    Notification::assertSentOnDemand(PaymentReceivedNotification::class);
});

it('rejects invalid xendit callback tokens without mutating payments', function (): void {
    config()->set('services.xendit.callback_token', 'valid-callback-token');

    $fixture = webhookFixture('Xendit');

    $response = $this->withHeaders([
        'x-callback-token' => 'invalid-token',
    ])->postJson(route('webhooks.xendit'), [
        'id' => 'xnd-event-001',
        'external_id' => $fixture['payment']->reference,
        'status' => 'PAID',
    ]);

    $response->assertStatus(400);

    $fixture['payment']->refresh();

    expect($fixture['payment']->status)->toBe('pending');
    expect(WebhookEvent::query()->firstOrFail()->status)->toBe('failed');
});
