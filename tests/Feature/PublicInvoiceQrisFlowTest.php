<?php

use App\Models\Customer;
use App\Models\Entity;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Notifications\PaymentReceivedNotification;
use App\Services\Payment\PaymentAllocationService;
use App\Support\EntityContext;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

function publicQrisFixture(string $provider = 'Midtrans'): array
{
    $entity = Entity::query()->create([
        'name' => 'Public QRIS Entity',
        'code' => 'PUB-QR',
        'currency' => 'IDR',
        'invoice_prefix' => 'PQR',
        'default_payment_terms' => 14,
        'default_tax_rate' => 0,
        'is_active' => true,
    ]);

    app(EntityContext::class)->setEntity($entity);

    $creator = User::factory()->create();
    $customer = Customer::query()->create([
        'name' => 'Public QRIS Customer',
        'email' => 'public-qris@example.test',
        'phone' => '+628111111111',
        'credit_balance' => '0.00',
        'is_active' => true,
    ]);

    $paymentMethod = PaymentMethod::query()->create([
        'name' => 'QRIS Gateway',
        'type' => 'qris',
        'provider' => $provider,
        'instructions' => 'Scan the QR to complete the payment.',
        'is_active' => true,
    ]);

    $invoice = Invoice::query()->create([
        'customer_id' => $customer->getKey(),
        'invoice_number' => 'PQR-001',
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

    return compact('entity', 'creator', 'customer', 'paymentMethod', 'invoice');
}

it('creates a midtrans qris payment from the public invoice page and confirms it via polling', function (): void {
    Notification::fake();

    config()->set('services.midtrans.server_key', 'midtrans-server-key');
    config()->set('services.midtrans.is_production', false);

    $fixture = publicQrisFixture('Midtrans');

    Http::fake([
        'https://api.sandbox.midtrans.com/v2/charge' => Http::response([
            'transaction_id' => 'midtrans-tx-001',
            'transaction_status' => 'pending',
            'order_id' => 'PQR-001-QR-ABCDEF1234',
            'gross_amount' => '100.00',
            'qr_string' => '00020101021126680016ID.CO.QRIS.WWW01189360091800000000000210MIDTRANS001',
            'expiry_time' => now()->addMinutes(15)->toIso8601String(),
            'actions' => [
                ['name' => 'generate-qr-code', 'url' => 'https://app.midtrans.com/qris/example'],
            ],
        ], 201),
        'https://api.sandbox.midtrans.com/v2/*/status' => Http::response([
            'transaction_id' => 'midtrans-tx-001',
            'transaction_status' => 'settlement',
            'order_id' => 'PQR-001-QR-ABCDEF1234',
            'gross_amount' => '100.00',
            'status_code' => '200',
        ], 200),
    ]);

    $response = $this->post(route('invoices.public.payments.store', ['token' => $fixture['invoice']->public_token]));

    $response->assertRedirect(route('invoices.public.show', ['token' => $fixture['invoice']->public_token]));

    $payment = Payment::query()->withoutGlobalScopes()->with('allocations')->firstOrFail();

    expect($payment->status)->toBe('pending');
    expect($payment->gateway_response['qr_string'])->toContain('000201');
    expect($payment->allocations)->toHaveCount(1);

    $this->get(route('invoices.public.show', ['token' => $fixture['invoice']->public_token]))
        ->assertOk()
        ->assertSee($payment->reference);

    $statusResponse = $this->getJson(route('invoices.public.payments.status', ['token' => $fixture['invoice']->public_token]));

    $statusResponse->assertOk();
    $statusResponse->assertJson([
        'success' => true,
        'data' => [
            'invoice_status' => 'paid',
        ],
    ]);

    $payment->refresh();
    $fixture['invoice']->refresh();

    expect($payment->status)->toBe('confirmed');
    expect($fixture['invoice']->status)->toBe('paid');
    expect($fixture['invoice']->amount_due)->toBe('0.00');

    Notification::assertSentOnDemand(PaymentReceivedNotification::class);
    Http::assertSentCount(2);
});
