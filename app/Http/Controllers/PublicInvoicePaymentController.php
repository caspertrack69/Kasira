<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\Payment\OnlinePaymentService;
use Illuminate\Http\RedirectResponse;

class PublicInvoicePaymentController extends Controller
{
    public function __construct(
        private readonly OnlinePaymentService $onlinePaymentService,
    ) {
    }

    public function store(string $token): RedirectResponse
    {
        $invoice = Invoice::query()
            ->withoutGlobalScopes()
            ->with(['entity', 'customer'])
            ->where('public_token', $token)
            ->firstOrFail();

        $payment = $this->onlinePaymentService->startQrisPayment($invoice);
        $paymentData = $this->onlinePaymentService->paymentData($payment);

        return redirect()
            ->route('invoices.public.show', ['token' => $token])
            ->with('status', $paymentData['qr_string'] ? 'QRIS payment is ready.' : 'Online payment checkout has been created.');
    }
}
