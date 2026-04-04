<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\WebhookEvent;
use App\Services\Payment\Gateways\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MidtransWebhookController extends Controller
{
    public function __invoke(Request $request, MidtransService $service): JsonResponse
    {
        $payload = $request->all();
        $signature = $request->string('signature_key')->toString();

        if (! $service->validateSignature($payload, $signature)) {
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        $event = WebhookEvent::query()->firstOrCreate(
            [
                'gateway' => 'midtrans',
                'event_id' => (string) ($payload['transaction_id'] ?? $payload['order_id'] ?? null),
            ],
            [
                'signature' => $signature,
                'payload' => $payload,
                'status' => 'received',
            ],
        );

        if ($event->status === 'processed') {
            return response()->json(['message' => 'Already processed']);
        }

        $invoiceNumber = (string) ($payload['order_id'] ?? '');
        $invoice = Payment::query()->where('reference', $invoiceNumber)->first();
        if ($invoice) {
            $invoice->update([
                'status' => 'confirmed',
                'gateway_response' => $payload,
                'confirmed_at' => now(),
            ]);
        }

        $event->update([
            'status' => 'processed',
            'processed_at' => now(),
        ]);

        return response()->json(['message' => 'OK']);
    }
}
