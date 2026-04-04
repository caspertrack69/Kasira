<?php

namespace App\Jobs;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class GenerateInvoicePdfJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $invoiceId)
    {
    }

    public function handle(): void
    {
        $invoice = Invoice::query()->withoutGlobalScopes()->with(['entity', 'customer', 'items'])->findOrFail($this->invoiceId);

        $pdf = Pdf::loadView('invoices.pdf', ['invoice' => $invoice]);

        $path = 'invoices/'.$invoice->entity_id.'/'.$invoice->invoice_number.'.pdf';
        Storage::disk('private')->put($path, $pdf->output());

        $invoice->update(['pdf_path' => $path]);
    }
}
