<?php

namespace App\Services\Billing;

use App\Enums\InvoiceStatus;
use App\Jobs\GenerateInvoicePdfJob;
use App\Jobs\SendInvoiceEmailJob;
use App\Models\Entity;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceStatusHistory;
use App\Models\RecurringTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RecurringInvoiceService
{
    public function __construct(
        private readonly InvoiceNumberService $numberService,
    ) {
    }

    public function generateDueTemplates(?\DateTimeInterface $date = null): int
    {
        $targetDate = $date ? now()->parse($date->format('Y-m-d')) : now()->startOfDay();

        $templates = RecurringTemplate::query()
            ->withoutGlobalScopes()
            ->where('is_active', true)
            ->whereDate('next_generate_date', '<=', $targetDate)
            ->get();

        $generated = 0;

        foreach ($templates as $template) {
            if (! $this->canGenerate($template, $targetDate)) {
                continue;
            }

            $this->generateFromTemplate($template);
            $generated++;
        }

        return $generated;
    }

    private function canGenerate(RecurringTemplate $template, \Carbon\CarbonInterface $date): bool
    {
        if ($template->end_date && $date->greaterThan($template->end_date)) {
            return false;
        }

        if ($template->occurrences_limit !== null && $template->occurrences_count >= $template->occurrences_limit) {
            return false;
        }

        return true;
    }

    private function generateFromTemplate(RecurringTemplate $template): void
    {
        $payload = $template->template_data;
        $entity = Entity::query()->findOrFail($template->entity_id);
        $invoiceId = null;
        $invoiceStatus = InvoiceStatus::Draft;

        DB::transaction(function () use ($template, $payload, $entity, &$invoiceId, &$invoiceStatus): void {
            $invoiceDate = now()->toDateString();
            $number = $this->numberService->generate($entity, now());
            $invoiceStatus = $template->auto_send ? InvoiceStatus::Sent : InvoiceStatus::Draft;

            $invoice = Invoice::query()->create([
                'entity_id' => $template->entity_id,
                'customer_id' => $template->customer_id,
                'recurring_template_id' => $template->getKey(),
                'invoice_number' => $number,
                'status' => $invoiceStatus->value,
                'invoice_date' => $invoiceDate,
                'due_date' => $payload['due_date'] ?? now()->addDays((int) ($entity->default_payment_terms ?? 30))->toDateString(),
                'subtotal' => $payload['subtotal'] ?? 0,
                'discount_total' => $payload['discount_total'] ?? 0,
                'tax_total' => $payload['tax_total'] ?? 0,
                'grand_total' => $payload['grand_total'] ?? 0,
                'amount_paid' => 0,
                'amount_due' => $payload['grand_total'] ?? 0,
                'currency' => $payload['currency'] ?? $entity->currency,
                'notes' => $payload['notes'] ?? null,
                'terms' => $payload['terms'] ?? null,
                'public_token' => (string) Str::ulid().Str::random(16),
                'sent_at' => $template->auto_send ? now() : null,
                'created_by' => $template->created_by,
            ]);

            foreach ($payload['items'] ?? [] as $index => $line) {
                InvoiceItem::query()->create([
                    'invoice_id' => $invoice->getKey(),
                    'item_id' => $line['item_id'] ?? null,
                    'description' => $line['description'] ?? 'Recurring item',
                    'quantity' => $line['quantity'] ?? 1,
                    'unit_price' => $line['unit_price'] ?? 0,
                    'discount_type' => $line['discount_type'] ?? null,
                    'discount_value' => $line['discount_value'] ?? null,
                    'discount_amount' => $line['discount_amount'] ?? 0,
                    'tax_id' => $line['tax_id'] ?? null,
                    'tax_rate' => $line['tax_rate'] ?? 0,
                    'tax_amount' => $line['tax_amount'] ?? 0,
                    'subtotal' => $line['subtotal'] ?? 0,
                    'sort_order' => $index,
                ]);
            }

            InvoiceStatusHistory::query()->create([
                'entity_id' => $invoice->entity_id,
                'invoice_id' => $invoice->getKey(),
                'to_status' => $invoiceStatus->value,
                'changed_by' => $template->created_by,
                'notes' => $template->auto_send
                    ? 'Invoice generated and automatically sent from recurring template'
                    : 'Invoice generated as draft from recurring template',
            ]);

            $template->update([
                'occurrences_count' => $template->occurrences_count + 1,
                'next_generate_date' => $this->nextDate($template),
                'is_active' => $this->shouldRemainActive($template),
            ]);

            $invoiceId = $invoice->getKey();
        });

        if (! $invoiceId) {
            return;
        }

        GenerateInvoicePdfJob::dispatch($invoiceId);

        if ($invoiceStatus === InvoiceStatus::Sent) {
            SendInvoiceEmailJob::dispatch($invoiceId);
        }
    }

    private function nextDate(RecurringTemplate $template): string
    {
        return match ($template->frequency) {
            'daily' => now()->parse($template->next_generate_date)->addDay()->toDateString(),
            'weekly' => now()->parse($template->next_generate_date)->addWeek()->toDateString(),
            'monthly' => now()->parse($template->next_generate_date)->addMonth()->toDateString(),
            'quarterly' => now()->parse($template->next_generate_date)->addMonths(3)->toDateString(),
            'annually' => now()->parse($template->next_generate_date)->addYear()->toDateString(),
            default => now()->parse($template->next_generate_date)->addMonth()->toDateString(),
        };
    }

    private function shouldRemainActive(RecurringTemplate $template): bool
    {
        if ($template->occurrences_limit === null) {
            return true;
        }

        return ($template->occurrences_count + 1) < $template->occurrences_limit;
    }
}
