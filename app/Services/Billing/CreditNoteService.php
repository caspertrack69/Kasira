<?php

namespace App\Services\Billing;

use App\Models\CreditNote;
use App\Models\Customer;
use App\Models\Invoice;
use App\Jobs\GenerateCreditNotePdfJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreditNoteService
{
    public function __construct(
        private readonly InvoiceBalanceService $invoiceBalanceService,
    ) {
    }

    public function issue(Invoice $invoice, string $amount, string $reason, int $userId): CreditNote
    {
        if (! in_array($invoice->status, ['partial', 'paid'], true)) {
            throw ValidationException::withMessages([
                'invoice_id' => 'Credit notes can only be issued for partial or paid invoices.',
            ]);
        }

        $normalizedAmount = number_format((float) $amount, 2, '.', '');
        if (bccomp($normalizedAmount, '0', 2) !== 1) {
            throw ValidationException::withMessages([
                'amount' => 'Credit note amount must be greater than zero.',
            ]);
        }

        $existingCredits = number_format((float) CreditNote::query()
            ->withoutGlobalScopes()
            ->where('invoice_id', $invoice->getKey())
            ->whereIn('status', ['issued', 'applied'])
            ->sum('amount'), 2, '.', '');

        if (bccomp(bcadd($existingCredits, $normalizedAmount, 2), (string) $invoice->grand_total, 2) === 1) {
            throw ValidationException::withMessages([
                'amount' => 'Credit note total cannot exceed the invoice grand total.',
            ]);
        }

        $beforeSnapshot = $this->invoiceBalanceService->snapshot($invoice);

        $creditNote = DB::transaction(function () use ($invoice, $normalizedAmount, $reason, $userId, $beforeSnapshot): CreditNote {
            $creditNote = CreditNote::query()->create([
                'entity_id' => $invoice->entity_id,
                'invoice_id' => $invoice->getKey(),
                'customer_id' => $invoice->customer_id,
                'credit_note_number' => $this->nextNumber($invoice),
                'amount' => $normalizedAmount,
                'reason' => $reason,
                'status' => 'applied',
                'created_by' => $userId,
            ]);

            $afterSnapshot = $this->invoiceBalanceService->apply($invoice);
            $delta = bcsub($afterSnapshot['overpayment'], $beforeSnapshot['overpayment'], 2);

            if (bccomp($delta, '0', 2) === 1) {
                $customer = Customer::query()->withoutGlobalScopes()->find($invoice->customer_id);
                if ($customer) {
                    $customer->update([
                        'credit_balance' => bcadd((string) $customer->credit_balance, $delta, 2),
                    ]);
                }
            }

            return $creditNote;
        });

        GenerateCreditNotePdfJob::dispatch($creditNote->getKey());

        return $creditNote;
    }

    private function nextNumber(Invoice $invoice): string
    {
        $sequence = CreditNote::query()
            ->withoutGlobalScopes()
            ->where('entity_id', $invoice->entity_id)
            ->whereYear('created_at', now()->year)
            ->count() + 1;

        return sprintf(
            'CN-%s-%s-%04d',
            strtoupper($invoice->entity?->invoice_prefix ?? 'ENT'),
            now()->format('Y'),
            $sequence,
        );
    }
}
