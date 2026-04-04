<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\WebhookEvent;
use App\Services\Payment\Gateways\XenditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class XenditWebhookController extends Controller
{
    public function __invoke(Request $request, XenditService $service): JsonResponse
    {
        $token = $request->header('x-callback-token');
        if (! $service->validateCallbackToken($token)) {
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        $payload = $request->all();

        $event = WebhookEvent::query()->firstOrCreate(
            [
                'gateway' => 'xendit',
                'event_id' => (string) ($payload['id'] ?? $payload['external_id'] ?? null),
            ],
            [
                'signature' => (string) $token,
                'payload' => $payload,
                'status' => 'received',
            ],
        );

        if ($event->status === 'processed') {
            return response()->json(['message' => 'Already processed']);
        }

        $reference = (string) ($payload['external_id'] ?? '');
        $payment = Payment::query()->where('reference', $reference)->first();
        if ($payment) {
            $payment->update([
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
