<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\Payment\OnlinePaymentService;
use Illuminate\Http\JsonResponse;

class PublicInvoicePaymentStatusController extends Controller
{
    public function __construct(
        private readonly OnlinePaymentService $onlinePaymentService,
    ) {
    }

    public function __invoke(string $token): JsonResponse
    {
        $invoice = Invoice::query()
            ->withoutGlobalScopes()
            ->where('public_token', $token)
            ->firstOrFail();

        $payment = $this->onlinePaymentService->syncInvoicePaymentStatus($invoice);
        $invoice->refresh();

        return response()->json([
            'success' => true,
            'data' => [
                'invoice_status' => $invoice->status,
                'amount_due' => $invoice->amount_due,
                'payment' => $this->onlinePaymentService->paymentData($payment),
            ],
            'message' => 'Payment status synchronized.',
            'errors' => null,
        ]);
    }
}
