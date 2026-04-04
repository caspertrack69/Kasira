<?php

namespace App\Services\Report;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Models\Invoice;
use App\Models\Payment;

class FinancialReportService
{
    public function summary(?string $entityId, string $from, string $to): array
    {
        $invoiceQuery = Invoice::query()
            ->withoutGlobalScopes()
            ->whereBetween('invoice_date', [$from, $to])
            ->whereNotIn('status', [
                InvoiceStatus::Draft->value,
                InvoiceStatus::Cancelled->value,
                InvoiceStatus::Void->value,
            ]);

        $paymentQuery = Payment::query()
            ->withoutGlobalScopes()
            ->whereBetween('payment_date', [$from, $to])
            ->where('status', PaymentStatus::Confirmed->value);

        if ($entityId) {
            $invoiceQuery->where('entity_id', $entityId);
            $paymentQuery->where('entity_id', $entityId);
        }

        $outstandingQuery = $invoiceQuery->clone()->where('amount_due', '>', 0);
        $agingQuery = $outstandingQuery->clone()->whereIn('status', [
            InvoiceStatus::Sent->value,
            InvoiceStatus::Partial->value,
            InvoiceStatus::Overdue->value,
        ]);

        return [
            'invoice_total' => $this->decimal($invoiceQuery->sum('grand_total')),
            'payment_total' => $this->decimal($paymentQuery->sum('amount')),
            'outstanding_total' => $this->decimal($outstandingQuery->sum('amount_due')),
            'aging' => [
                'current' => $this->decimal($agingQuery->clone()->whereDate('due_date', '>=', now()->toDateString())->sum('amount_due')),
                '30' => $this->decimal($agingQuery->clone()->whereBetween('due_date', [now()->subDays(30)->toDateString(), now()->subDay()->toDateString()])->sum('amount_due')),
                '60' => $this->decimal($agingQuery->clone()->whereBetween('due_date', [now()->subDays(60)->toDateString(), now()->subDays(31)->toDateString()])->sum('amount_due')),
                '90_plus' => $this->decimal($agingQuery->clone()->whereDate('due_date', '<', now()->subDays(60)->toDateString())->sum('amount_due')),
            ],
        ];
    }

    private function decimal(mixed $value): string
    {
        return number_format((float) ($value ?? 0), 2, '.', '');
    }
}
