<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Jobs\GenerateInvoicePdfJob;
use App\Jobs\SendInvoiceEmailJob;
use App\Models\Invoice;
use App\Models\InvoiceStatusHistory;
use Illuminate\Http\RedirectResponse;

class InvoiceSendController extends Controller
{
    public function __invoke(Invoice $invoice): RedirectResponse
    {
        $this->authorize('send', $invoice);

        if ($invoice->status !== InvoiceStatus::Draft->value) {
            return back()->withErrors(['invoice' => 'Only draft invoices can be sent.']);
        }

        $invoice->update([
            'status' => InvoiceStatus::Sent->value,
            'sent_at' => now(),
        ]);

        InvoiceStatusHistory::query()->create([
            'entity_id' => $invoice->entity_id,
            'invoice_id' => $invoice->getKey(),
            'from_status' => InvoiceStatus::Draft->value,
            'to_status' => InvoiceStatus::Sent->value,
            'changed_by' => request()->user()->getKey(),
            'notes' => 'Invoice sent to customer',
        ]);

        GenerateInvoicePdfJob::dispatch($invoice->getKey());
        SendInvoiceEmailJob::dispatch($invoice->getKey());

        return back()->with('status', 'Invoice queued for delivery.');
    }
}
