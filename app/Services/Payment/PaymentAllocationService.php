<?php

namespace App\Services\Payment;

use App\Enums\PaymentStatus;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\Billing\InvoiceBalanceService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentAllocationService
{
    public function __construct(
        private readonly InvoiceBalanceService $invoiceBalanceService,
    ) {
    }

    /**
     * @param array<string,string|int|float> $allocations
     */
    public function allocate(Payment $payment, array $allocations): void
    {
        $normalizedAllocations = [];
        $allocatedTotal = '0.00';

        foreach ($allocations as $invoiceId => $amount) {
            $value = number_format((float) $amount, 2, '.', '');
            if ((float) $value <= 0) {
                continue;
            }

            $invoice = Invoice::query()
                ->withoutGlobalScopes()
                ->where('entity_id', $payment->entity_id)
                ->findOrFail($invoiceId);

            if (bccomp($value, (string) $invoice->amount_due, 2) === 1) {
                throw ValidationException::withMessages([
                    "allocations.$invoiceId" => 'Allocated amount cannot exceed the invoice outstanding balance.',
                ]);
            }

            $normalizedAllocations[$invoice->getKey()] = $value;
            $allocatedTotal = bcadd($allocatedTotal, $value, 2);
        }

        if (bccomp($allocatedTotal, (string) $payment->amount, 2) === 1) {
            throw ValidationException::withMessages([
                'allocations' => 'Allocated total cannot exceed the payment amount.',
            ]);
        }

        DB::transaction(function () use ($payment, $normalizedAllocations): void {
            foreach ($normalizedAllocations as $invoiceId => $value) {
                $payment->allocations()->updateOrCreate(
                    ['invoice_id' => $invoiceId],
                    ['amount' => $value],
                );
            }
        });
    }

    public function applyConfirmedAllocations(Payment $payment): void
    {
        $invoiceIds = $payment->allocations()->pluck('invoice_id')->unique()->values();

        if ($invoiceIds->isEmpty()) {
            return;
        }

        $invoices = Invoice::query()
            ->withoutGlobalScopes()
            ->where('entity_id', $payment->entity_id)
            ->whereIn('id', $invoiceIds)
            ->get();

        foreach ($invoices as $invoice) {
            $this->invoiceBalanceService->apply($invoice);
        }
    }
}
