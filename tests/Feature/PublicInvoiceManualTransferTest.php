<?php

use App\Models\Customer;
use App\Models\Entity;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Support\EntityContext;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

function publicManualTransferFixture(): array
{
    $entity = Entity::query()->create([
        'name' => 'Public Manual Transfer Entity',
        'code' => 'PUB-MAN',
        'currency' => 'IDR',
        'invoice_prefix' => 'PMT',
        'default_payment_terms' => 14,
        'default_tax_rate' => 0,
        'is_active' => true,
    ]);

    app(EntityContext::class)->setEntity($entity);

    $creator = User::factory()->create();
    $customer = Customer::query()->create([
        'name' => 'Public Manual Customer',
        'email' => 'public-manual@example.test',
        'phone' => '+628222222222',
        'credit_balance' => '0.00',
        'is_active' => true,
    ]);

    $paymentMethod = PaymentMethod::query()->create([
        'name' => 'BCA Transfer',
        'type' => 'bank_transfer',
        'provider' => 'manual',
        'account_name' => 'PT Kasira',
        'account_number' => '1234567890',
        'instructions' => 'Transfer sesuai nominal invoice.',
        'is_active' => true,
    ]);

    $invoice = Invoice::query()->create([
        'customer_id' => $customer->getKey(),
        'invoice_number' => 'PMT-001',
        'status' => 'sent',
        'invoice_date' => now()->toDateString(),
        'due_date' => now()->addDays(14)->toDateString(),
        'subtotal' => '250000.00',
        'discount_total' => '0.00',
        'tax_total' => '0.00',
        'grand_total' => '250000.00',
        'amount_paid' => '0.00',
        'amount_due' => '250000.00',
        'currency' => 'IDR',
        'public_token' => (string) Str::ulid(),
        'created_by' => $creator->getKey(),
    ]);

    return compact('entity', 'creator', 'customer', 'paymentMethod', 'invoice');
}

it('shows bank transfer methods on public invoice and allows proof upload', function (): void {
    Storage::fake('private');

    $fixture = publicManualTransferFixture();

    $this->get(route('invoices.public.show', ['token' => $fixture['invoice']->public_token]))
        ->assertOk()
        ->assertSee('BCA Transfer')
        ->assertSee('1234567890')
        ->assertSee('Transfer sesuai nominal invoice.');

    $response = $this->post(route('invoices.public.payments.bank-transfer.store', ['token' => $fixture['invoice']->public_token]), [
        'payment_method_id' => $fixture['paymentMethod']->getKey(),
        'amount' => '250000',
        'payment_date' => now()->toDateString(),
        'reference' => 'TRX-INV-PMT-001',
        'notes' => 'Sudah ditransfer via m-banking.',
        'proof' => UploadedFile::fake()->image('proof-transfer.jpg'),
    ]);

    $response->assertRedirect(route('invoices.public.show', ['token' => $fixture['invoice']->public_token]));

    $payment = Payment::query()->withoutGlobalScopes()->with('allocations')->firstOrFail();

    expect($payment->entity_id)->toBe($fixture['entity']->getKey());
    expect($payment->customer_id)->toBe($fixture['customer']->getKey());
    expect($payment->payment_method_id)->toBe($fixture['paymentMethod']->getKey());
    expect($payment->status)->toBe('pending');
    expect($payment->amount)->toBe('250000.00');
    expect($payment->allocations)->toHaveCount(1);
    expect($payment->allocations->first()?->invoice_id)->toBe($fixture['invoice']->getKey());

    Storage::disk('private')->assertExists($payment->proof_path);
});
