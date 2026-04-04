<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateInvoicePdfJob;
use App\Models\Invoice;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvoicePdfController extends Controller
{
    public function __invoke(Invoice $invoice): StreamedResponse
    {
        $this->authorize('view', $invoice);

        if (! $invoice->pdf_path || ! Storage::disk('private')->exists($invoice->pdf_path)) {
            GenerateInvoicePdfJob::dispatchSync($invoice->getKey());
            $invoice->refresh();
        }

        abort_unless($invoice->pdf_path && Storage::disk('private')->exists($invoice->pdf_path), 404);

        return Storage::disk('private')->download($invoice->pdf_path, $invoice->invoice_number.'.pdf');
    }
}
