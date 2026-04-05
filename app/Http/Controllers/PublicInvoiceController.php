<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\Payment\OnlinePaymentService;
use Illuminate\View\View;

class PublicInvoiceController extends Controller
{
    public function __construct(
        private readonly OnlinePaymentService $onlinePaymentService,
    ) {
    }

    public function show(string $token): View
    {
        $invoice = Invoice::query()
            ->withoutGlobalScopes()
            ->with(['entity', 'customer', 'items'])
            ->where('public_token', $token)
            ->firstOrFail();

        $onlinePayment = $this->onlinePaymentService->currentGatewayPayment($invoice);

        return view('invoices.public', [
            'invoice' => $invoice,
            'onlinePayment' => $onlinePayment,
            'paymentData' => $this->onlinePaymentService->paymentData($onlinePayment),
        ]);
    }
}
