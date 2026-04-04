<?php

use App\Models\Customer;
use App\Models\Entity;
use App\Models\Invoice;
use App\Models\User;
use App\Support\EntityContext;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

it('downloads a public invoice pdf and generates it on demand', function (): void {
    Storage::fake('private');

    $entity = Entity::query()->create([
        'name' => 'Public Docs Entity',
        'code' => 'PUB-DOC',
        'currency' => 'IDR',
        'invoice_prefix' => 'PUB',
        'default_payment_terms' => 14,
        'default_tax_rate' => 0,
        'is_active' => true,
    ]);

    app(EntityContext::class)->setEntity($entity);

    $user = User::factory()->create();
    $customer = Customer::query()->create([
        'name' => 'Public Docs Customer',
        'email' => 'public-docs@example.test',
        'credit_balance' => '0.00',
        'is_active' => true,
    ]);

    $invoice = Invoice::query()->create([
        'customer_id' => $customer->getKey(),
        'invoice_number' => 'PUB-001',
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
        'created_by' => $user->getKey(),
    ]);

    $response = $this->get(route('invoices.public.download', ['token' => $invoice->public_token]));

    $response->assertOk();
    $response->assertHeader('content-disposition');

    $invoice->refresh();

    expect($invoice->pdf_path)->not->toBeNull();
    Storage::disk('private')->assertExists($invoice->pdf_path);
});
