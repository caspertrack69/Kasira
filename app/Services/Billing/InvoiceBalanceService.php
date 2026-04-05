<?php

namespace App\Services\Billing;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Models\CreditNote;
use App\Models\Invoice;
use App\Models\PaymentAllocation;

class InvoiceBalanceService
{
    /**
     * @return array{confirmed_payments:string,applied_credits:string,net_receivable:string,amount_due:string,overpayment:string}
     */
    public function snapshot(Invoice $invoice): array
    {
        $confirmedPayments = $this->decimal(
            PaymentAllocation::query()
                ->join('payments', 'payments.id', '=', 'payment_allocations.payment_id')
                ->where('payments.status', PaymentStatus::Confirmed->value)
                ->where('payment_allocations.invoice_id', $invoice->getKey())
                ->sum('payment_allocations.amount'),
        );

        $appliedCredits = $this->decimal(
            CreditNote::query()
                ->withoutGlobalScopes()
                ->where('invoice_id', $invoice->getKey())
                ->whereIn('status', ['issued', 'applied'])
                ->sum('amount'),
        );

        $netReceivable = $this->maxZero(bcsub((string) $invoice->grand_total, $appliedCredits, 2));
        $amountDue = $this->maxZero(bcsub($netReceivable, $confirmedPayments, 2));
        $overpayment = $this->maxZero(bcsub($confirmedPayments, $netReceivable, 2));

        return [
            'confirmed_payments' => $confirmedPayments,
            'applied_credits' => $appliedCredits,
            'net_receivable' => $netReceivable,
            'amount_due' => $amountDue,
            'overpayment' => $overpayment,
        ];
    }

    /**
     * @return array{confirmed_payments:string,applied_credits:string,net_receivable:string,amount_due:string,overpayment:string}
     */
    public function apply(Invoice $invoice): array
    {
        $snapshot = $this->snapshot($invoice);
        $shouldMarkPaid = bccomp($snapshot['amount_due'], '0.00', 2) === 0
            && (bccomp($snapshot['confirmed_payments'], '0.00', 2) === 1 || bccomp($snapshot['applied_credits'], '0.00', 2) === 1);

        $invoice->forceFill([
            'amount_paid' => $snapshot['confirmed_payments'],
            'amount_due' => $snapshot['amount_due'],
            'status' => $this->resolveStatus($invoice, $snapshot, $shouldMarkPaid),
            'paid_at' => $shouldMarkPaid ? ($invoice->paid_at ?? now()) : null,
        ])->save();

        return $snapshot;
    }

    /**
     * @param array{confirmed_payments:string,applied_credits:string,net_receivable:string,amount_due:string,overpayment:string} $snapshot
     */
    private function resolveStatus(Invoice $invoice, array $snapshot, bool $shouldMarkPaid): string
    {
        if (in_array($invoice->status, [InvoiceStatus::Cancelled->value, InvoiceStatus::Void->value], true)) {
            return $invoice->status;
        }

        if ($shouldMarkPaid) {
            return InvoiceStatus::Paid->value;
        }

        if (bccomp($snapshot['confirmed_payments'], '0.00', 2) === 1) {
            return $invoice->due_date && $invoice->due_date->isPast()
                ? InvoiceStatus::Overdue->value
                : InvoiceStatus::Partial->value;
        }

        return $invoice->due_date && $invoice->due_date->isPast()
            ? InvoiceStatus::Overdue->value
            : InvoiceStatus::Sent->value;
    }

    private function decimal(mixed $value): string
    {
        return number_format((float) ($value ?? 0), 2, '.', '');
    }

    private function maxZero(string $value): string
    {
        return bccomp($value, '0', 2) === -1 ? '0.00' : $this->decimal($value);
    }
}
