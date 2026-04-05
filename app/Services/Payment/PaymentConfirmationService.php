<?php

namespace App\Services\Payment;

use App\Enums\PaymentStatus;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\NotificationLog;
use App\Models\Payment;
use App\Notifications\PaymentReceivedNotification;
use App\Services\Billing\InvoiceBalanceService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class PaymentConfirmationService
{
    public function __construct(
        private readonly PaymentAllocationService $allocationService,
        private readonly InvoiceBalanceService $invoiceBalanceService,
    ) {
    }

    public function confirm(Payment $payment, ?int $userId = null): void
    {
        if ($payment->status === PaymentStatus::Confirmed->value) {
            return;
        }

        $invoiceIds = $payment->allocations()->pluck('invoice_id')->unique()->values();
        $beforeSnapshots = Invoice::query()
            ->withoutGlobalScopes()
            ->whereIn('id', $invoiceIds)
            ->get()
            ->mapWithKeys(fn (Invoice $invoice): array => [$invoice->getKey() => $this->invoiceBalanceService->snapshot($invoice)]);

        DB::transaction(function () use ($payment, $userId, $invoiceIds, $beforeSnapshots): void {
            $payment->update([
                'status' => PaymentStatus::Confirmed->value,
                'confirmed_by' => $userId,
                'confirmed_at' => now(),
            ]);

            $this->allocationService->applyConfirmedAllocations($payment);

            $invoices = Invoice::query()
                ->withoutGlobalScopes()
                ->whereIn('id', $invoiceIds)
                ->get();

            foreach ($invoices as $invoice) {
                $afterSnapshot = $this->invoiceBalanceService->snapshot($invoice);
                $beforeSnapshot = $beforeSnapshots[$invoice->getKey()] ?? ['overpayment' => '0.00'];
                $delta = bcsub($afterSnapshot['overpayment'], $beforeSnapshot['overpayment'], 2);

                if (bccomp($delta, '0', 2) === 1) {
                    $customer = Customer::query()->withoutGlobalScopes()->find($invoice->customer_id);
                    if ($customer) {
                        $customer->update([
                            'credit_balance' => bcadd((string) $customer->credit_balance, $delta, 2),
                        ]);
                    }
                }
            }
        });

        $payment->loadMissing('customer');

        if (! $payment->customer?->email) {
            return;
        }

        Notification::route('mail', $payment->customer->email)->notify(new PaymentReceivedNotification($payment));

        NotificationLog::query()->create([
            'entity_id' => $payment->entity_id,
            'notifiable_type' => $payment->customer::class,
            'notifiable_id' => $payment->customer->getKey(),
            'subject_type' => $payment::class,
            'subject_id' => $payment->getKey(),
            'channel' => 'email',
            'event_type' => 'payment_received',
            'recipient' => $payment->customer->email,
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }
}
