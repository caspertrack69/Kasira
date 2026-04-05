<?php

use App\Models\CreditNote;
use App\Models\Customer;
use App\Models\Entity;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Services\Payment\PaymentAllocationService;
use App\Services\Payment\PaymentConfirmationService;
use App\Support\EntityContext;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

function creditNoteFixture(string $paymentAmount = '100.00'): array
{
    $entity = Entity::query()->create([
        'name' => 'Credit Note Entity',
        'code' => 'CN-ENT',
        'currency' => 'IDR',
        'invoice_prefix' => 'CNT',
        'default_payment_terms' => 14,
        'default_tax_rate' => 0,
        'is_active' => true,
    ]);

    app(EntityContext::class)->setEntity($entity);

    $creator = User::factory()->create();
    $customer = Customer::query()->create([
        'name' => 'Credit Note Customer',
        'email' => 'credit-note@example.test',
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

    $invoice = Invoice::query()->create([
        'customer_id' => $customer->getKey(),
        'invoice_number' => 'CNT-001',
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
        'payment_number' => 'PAY-CN-001',
        'amount' => $paymentAmount,
        'payment_date' => now()->toDateString(),
        'reference' => 'CN-REF-001',
        'status' => 'pending',
        'created_by' => $creator->getKey(),
    ]);

    app(PaymentAllocationService::class)->allocate($payment, [
        $invoice->getKey() => $paymentAmount,
    ]);

    app(PaymentConfirmationService::class)->confirm($payment, $creator->getKey());

    return compact('entity', 'creator', 'customer', 'invoice');
}

function grantCreditNotePermissions(Entity $entity): User
{
    $user = User::factory()->create();

    foreach (['credit-notes.manage', 'credit-notes.view'] as $permissionName) {
        Permission::query()->firstOrCreate([
            'name' => $permissionName,
            'guard_name' => 'web',
        ]);
    }

    if (function_exists('setPermissionsTeamId')) {
        setPermissionsTeamId($entity->getKey());
    }

    $role = Role::query()->create([
        'name' => 'credit_note_admin',
        'guard_name' => 'web',
        'entity_id' => $entity->getKey(),
    ]);
    $role->givePermissionTo(['credit-notes.manage', 'credit-notes.view']);

    $user->entities()->attach($entity->getKey(), [
        'role' => 'credit_note_admin',
        'assigned_by' => $user->getKey(),
    ]);
    $user->assignRole($role);
    $user->unsetRelation('roles')->unsetRelation('permissions');

    return $user;
}

it('applies a credit note against a partial invoice without creating customer overpayment', function (): void {
    $fixture = creditNoteFixture('40.00');
    $user = grantCreditNotePermissions($fixture['entity']);

    $response = $this
        ->actingAs($user)
        ->withSession(['active_entity_id' => $fixture['entity']->getKey()])
        ->post(route('credit-notes.store'), [
            'invoice_id' => $fixture['invoice']->getKey(),
            'amount' => '20.00',
            'reason' => 'Service adjustment',
        ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();

    $fixture['invoice']->refresh();
    $fixture['customer']->refresh();

    expect($fixture['invoice']->status)->toBe('partial');
    expect($fixture['invoice']->amount_due)->toBe('40.00');
    expect($fixture['customer']->credit_balance)->toBe('0.00');
    expect(CreditNote::query()->firstOrFail()->status)->toBe('applied');
});

it('creates customer credit and a downloadable pdf for paid invoices', function (): void {
    Storage::fake('private');

    $fixture = creditNoteFixture('100.00');
    $user = grantCreditNotePermissions($fixture['entity']);

    $response = $this
        ->actingAs($user)
        ->withSession(['active_entity_id' => $fixture['entity']->getKey()])
        ->post(route('credit-notes.store'), [
            'invoice_id' => $fixture['invoice']->getKey(),
            'amount' => '20.00',
            'reason' => 'Refund adjustment',
        ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();

    $fixture['invoice']->refresh();
    $fixture['customer']->refresh();
    $creditNote = CreditNote::query()->firstOrFail();

    expect($fixture['invoice']->status)->toBe('paid');
    expect($fixture['invoice']->amount_due)->toBe('0.00');
    expect($fixture['customer']->credit_balance)->toBe('20.00');
    expect($creditNote->status)->toBe('applied');

    $downloadResponse = $this
        ->actingAs($user)
        ->withSession(['active_entity_id' => $fixture['entity']->getKey()])
        ->get(route('credit-notes.pdf', $creditNote));

    $downloadResponse->assertOk();
    $downloadResponse->assertDownload($creditNote->credit_note_number.'.pdf');

    $creditNote->refresh();
    expect($creditNote->pdf_path)->not->toBeNull();
    Storage::disk('private')->assertExists($creditNote->pdf_path);
});
