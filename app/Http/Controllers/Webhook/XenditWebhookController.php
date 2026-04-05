<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\WebhookEvent;
use App\Services\Payment\Gateways\XenditService;
use App\Services\Payment\OnlinePaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class XenditWebhookController extends Controller
{
    public function __invoke(Request $request, XenditService $service, OnlinePaymentService $onlinePaymentService): JsonResponse
    {
        $payload = $request->all();
        $token = $request->header('x-callback-token');
        $eventId = (string) ($payload['id'] ?? $payload['external_id'] ?? sha1(json_encode($payload)));

        $event = WebhookEvent::query()->firstOrCreate(
            [
                'gateway' => 'xendit',
                'event_id' => $eventId,
            ],
            [
                'signature' => (string) $token,
                'payload' => $payload,
                'status' => 'received',
            ],
        );

        $event->update([
            'signature' => (string) $token,
            'payload' => $payload,
        ]);

        if (! $service->validateCallbackToken($token)) {
            $event->update([
                'status' => 'failed',
                'error_message' => 'Invalid callback token',
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
