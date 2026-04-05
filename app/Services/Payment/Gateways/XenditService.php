<?php

namespace App\Services\Payment\Gateways;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class XenditService
{
    public function validateCallbackToken(?string $token): bool
    {
        $configured = (string) config('services.xendit.callback_token');

        return $configured !== '' && $token !== null && hash_equals($configured, $token);
    }

    public function createQrisPayment(Invoice $invoice, PaymentMethod $paymentMethod, string $paymentNumber, string $reference): array
    {
        $payload = [
            'external_id' => $reference,
            'amount' => (float) $invoice->amount_due,
            'description' => 'Invoice '.$invoice->invoice_number,
            'invoice_duration' => 172800,
            'currency' => $invoice->currency,
            'success_redirect_url' => route('invoices.public.show', ['token' => $invoice->public_token]),
            'failure_redirect_url' => route('invoices.public.show', ['token' => $invoice->public_token]),
            'should_send_email' => false,
            'customer' => array_filter([
                'given_names' => $invoice->customer?->name,
                'email' => $invoice->customer?->email,
                'mobile_number' => $invoice->customer?->phone,
            ]),
            'items' => [
                [
                    'name' => 'Invoice '.$invoice->invoice_number,
                    'quantity' => 1,
                    'price' => (float) $invoice->amount_due,
                    'category' => 'invoice',
                ],
            ],
            'metadata' => [
                'invoice_number' => $invoice->invoice_number,
                'payment_number' => $paymentNumber,
                'payment_method' => $paymentMethod->name,
            ],
        ];

        $response = $this->request()->post('/v2/invoices', $payload)->throw()->json();

        return $this->normalizeStatusPayload($reference, $response);
    }

    public function fetchPaymentStatus(Payment $payment): array
    {
        $externalPaymentId = $payment->gateway_response['external_payment_id'] ?? null;
        if (! $externalPaymentId) {
            return [];
        }

        $response = $this->request()->get('/v2/invoices/'.$externalPaymentId)->throw()->json();

        return $this->normalizeStatusPayload((string) $payment->reference, $response);
    }

    public function normalizeWebhookPayload(array $payload): array
    {
        $reference = (string) ($payload['external_id'] ?? '');

        return $this->normalizeStatusPayload($reference, $payload);
    }

    private function request(): PendingRequest
    {
        return Http::acceptJson()
            ->withBasicAuth((string) config('services.xendit.secret_key'), '')
            ->baseUrl('https://api.xendit.co');
    }

    private function normalizeStatusPayload(string $reference, array $payload): array
    {
        $rawStatus = Str::lower((string) ($payload['status'] ?? 'pending'));

        return [
            'gateway' => 'xendit',
            'reference' => $reference,
            'external_payment_id' => $payload['id'] ?? ($payload['payment_request_id'] ?? null),
            'gateway_status' => $rawStatus,
            'normalized_status' => match ($rawStatus) {
                'paid', 'settled', 'succeeded' => 'confirmed',
                'expired', 'cancelled', 'failed' => 'cancelled',
                default => 'pending',
            },
            'qr_string' => $payload['qr_string'] ?? null,
            'qr_url' => $payload['qr_url'] ?? null,
            'checkout_url' => $payload['invoice_url'] ?? ($payload['checkout_url'] ?? null),
            'expires_at' => $this->normalizeDate($payload['expiry_date'] ?? ($payload['expires_at'] ?? null)),
            'provider_payload' => $payload,
        ];
    }

    private function normalizeDate(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        return now()->parse($value)->toIso8601String();
    }
}
