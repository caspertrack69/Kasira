<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\WebhookEvent;
use App\Services\Payment\Gateways\MidtransService;
use App\Services\Payment\OnlinePaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MidtransWebhookController extends Controller
{
    public function __invoke(Request $request, MidtransService $service, OnlinePaymentService $onlinePaymentService): JsonResponse
    {
        $payload = $request->all();
        $signature = $request->string('signature_key')->toString();
        $eventId = (string) ($payload['transaction_id'] ?? $payload['order_id'] ?? sha1(json_encode($payload)));

        $event = WebhookEvent::query()->firstOrCreate(
            [
                'gateway' => 'midtrans',
                'event_id' => $eventId,
            ],
            [
                'signature' => $signature,
                'payload' => $payload,
                'status' => 'received',
            ],
        );

        $event->update([
            'signature' => $signature,
            'payload' => $payload,
        ]);

        if (! $service->validateSignature($payload, $signature)) {
            $event->update([
                'status' => 'failed',
                'error_message' => 'Invalid signature',
            ]);

            return response()->json(['message' => 'Invalid signature'], 400);
        }

        if ($event->status === 'processed') {
            return response()->json(['message' => 'Already processed']);
        }

        try {
            $statusPayload = $service->normalizeWebhookPayload($payload);
            $payment = Payment::query()
                ->withoutGlobalScopes()
                ->where('reference', $statusPayload['reference'] ?? null)
                ->first();

            if ($payment) {
                $payment = $onlinePaymentService->applyGatewayStatusUpdate($payment, $statusPayload);
            }

            $event->update([
                'entity_id' => $payment?->entity_id,
                'status' => 'processed',
                'processed_at' => now(),
                'error_message' => null,
            ]);

            return response()->json(['message' => 'OK']);
        } catch (\Throwable $exception) {
            report($exception);

            $event->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
            ]);

            return response()->json(['message' => 'Failed'], 500);
        }
    }
}
