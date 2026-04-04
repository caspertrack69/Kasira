<?php

use App\Jobs\SendOverdueReminderJob;
use App\Models\Customer;
use App\Models\Entity;
use App\Models\Invoice;
use App\Models\User;
use App\Support\EntityContext;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

it('queues reminders only for configured overdue offsets', function (): void {
    $entity = Entity::query()->create([
        'name' => 'Reminder Entity',
        'code' => 'REM-ENT',
        'currency' => 'IDR',
        'invoice_prefix' => 'REM',
        'default_payment_terms' => 14,
        'default_tax_rate' => 0,
        'reminder_days' => [3, 7],
        'is_active' => true,
    ]);

    app(EntityContext::class)->setEntity($entity);

    $user = User::factory()->create();
    $customer = Customer::query()->create([
        'name' => 'Reminder Customer',
        'email' => 'reminder@example.test',
        'credit_balance' => '0.00',
        'is_active' => true,
    ]);

    $shouldQueue = Invoice::query()->create([
        'customer_id' => $customer->getKey(),
        'invoice_number' => 'REM-001',
        'status' => 'overdue',
        'invoice_date' => now()->subDays(10)->toDateString(),
        'due_date' => now()->subDays(3)->toDateString(),
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

    Invoice::query()->create([
        'customer_id' => $customer->getKey(),
        'invoice_number' => 'REM-002',
        'status' => 'overdue',
        'invoice_date' => now()->subDays(10)->toDateString(),
        'due_date' => now()->subDays(5)->toDateString(),
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

    Queue::fake();

    Artisan::call('kasira:notifications:dispatch-reminders');

    Queue::assertPushed(SendOverdueReminderJob::class, fn (SendOverdueReminderJob $job): bool => $job->invoiceId === $shouldQueue->getKey());
    Queue::assertPushed(SendOverdueReminderJob::class, 1);
});
