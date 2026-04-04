<?php

namespace App\Services\Payment;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use Illuminate\Support\Facades\DB;

class PaymentAllocationService
{
    /**
     * @param array<string,string|int|float> $allocations
     */
    public function allocate(Payment $payment, array $allocations): void
    {
        DB::transaction(function () use ($payment, $allocations): void {
            foreach ($allocations as $invoiceId => $amount) {
                $value = number_format((float) $amount, 2, '.', '');
                if ((float) $value <= 0) {
                    continue;
                }

                $invoice = Invoice::query()
                    ->withoutGlobalScopes()
                    ->where('entity_id', $payment->entity_id)
                    ->findOrFail($invoiceId);

                $payment->allocations()->updateOrCreate(
                    ['invoice_id' => $invoice->getKey()],
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
            ->get()
            ->keyBy('id');

        $totals = PaymentAllocation::query()
            ->selectRaw('invoice_id, SUM(payment_allocations.amount) as total_amount')
            ->join('payments', 'payments.id', '=', 'payment_allocations.payment_id')
            ->where('payments.status', PaymentStatus::Confirmed->value)
            ->whereIn('payment_allocations.invoice_id', $invoiceIds)
            ->groupBy('payment_allocations.invoice_id')
            ->pluck('total_amount', 'invoice_id');

        foreach ($invoices as $invoice) {
            $amountPaid = number_format((float) ($totals[$invoice->getKey()] ?? 0), 2, '.', '');
            $amountDue = bcsub((string) $invoice->grand_total, $amountPaid, 2);
            $isPaid = bccomp($amountDue, '0', 2) <= 0;

            $invoice->forceFill([
                'amount_paid' => $amountPaid,
                'amount_due' => $isPaid ? '0.00' : $amountDue,
                'status' => $isPaid ? InvoiceStatus::Paid->value : InvoiceStatus::Partial->value,
                'paid_at' => $isPaid ? now() : null,
            ])->save();
        }
    }
}

