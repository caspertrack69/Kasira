<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\PaymentMethod;
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
        $bankTransferMethods = PaymentMethod::query()
            ->withoutGlobalScopes()
            ->where('entity_id', $invoice->entity_id)
            ->where('type', 'bank_transfer')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('invoices.public', [
            'invoice' => $invoice,
            'onlinePayment' => $onlinePayment,
            'paymentData' => $this->onlinePaymentService->paymentData($onlinePayment),
            'bankTransferMethods' => $bankTransferMethods,
        ]);
    }
}
