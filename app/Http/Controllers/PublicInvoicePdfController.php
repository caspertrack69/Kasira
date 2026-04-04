<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateInvoicePdfJob;
use App\Models\Invoice;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicInvoicePdfController extends Controller
{
    public function __invoke(string $token): StreamedResponse
    {
        $invoice = Invoice::query()
            ->withoutGlobalScopes()
            ->where('public_token', $token)
            ->firstOrFail();

        if (! $invoice->pdf_path || ! Storage::disk('private')->exists($invoice->pdf_path)) {
            GenerateInvoicePdfJob::dispatchSync($invoice->getKey());
            $invoice->refresh();
        }

        abort_unless($invoice->pdf_path && Storage::disk('private')->exists($invoice->pdf_path), 404);

        return Storage::disk('private')->download($invoice->pdf_path, $invoice->invoice_number.'.pdf');
    }
}
