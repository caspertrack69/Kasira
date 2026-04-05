<?php

namespace App\Services\Payment\Gateways;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MidtransService
{
    public function validateSignature(array $payload, ?string $signature): bool
    {
        $serverKey = (string) config('services.midtrans.server_key');
        if ($serverKey === '' || $signature === null) {
            return false;
        }

        $orderId = (string) ($payload['order_id'] ?? '');
        $statusCode = (string) ($payload['status_code'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');

        $expected = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        return hash_equals($expected, $signature);
    }

    public function createQrisPayment(Invoice $invoice, PaymentMethod $paymentMethod, string $paymentNumber, string $reference): array
    {
        $payload = [
            'payment_type' => 'qris',
            'transaction_details' => [
                'order_id' => $reference,
                'gross_amount' => (int) round((float) $invoice->amount_due),
            ],
            'customer_details' => array_filter([
                'first_name' => $invoice->customer?->name,
                'email' => $invoice->customer?->email,
                'phone' => $invoice->customer?->phone,
            ]),
            'item_details' => [
                [
                    'id' => $invoice->invoice_number,
                    'price' => (int) round((float) $invoice->amount_due),
                    'quantity' => 1,
                    'name' => 'Invoice '.$invoice->invoice_number,
                ],
            ],
            'qris' => [
                'acquirer' => $this->acquirer($paymentMethod),
            ],
            'custom_expiry' => [
                'expiry_duration' => 15,
                'unit' => 'minute',
            ],
        ];

        $response = $this->request()->post('/v2/charge', $payload)->throw()->json();

        return $this->normalizeCreatePayload($reference, $response);
    }

    public function fetchPaymentStatus(Payment $payment): array
    {
        $reference = (string) $payment->reference;
        if ($reference === '') {
            return [];
        }

        $response = $this->request()->get('/v2/'.$reference.'/status')->throw()->json();

        return $this->normalizeStatusPayload($reference, $response);
    }

    public function normalizeWebhookPayload(array $payload): array
    {
        $reference = (string) ($payload['order_id'] ?? '');

        return $this->normalizeStatusPayload($reference, $payload);
    }

    private function request(): PendingRequest
    {
        return Http::acceptJson()
            ->withBasicAuth((string) config('services.midtrans.server_key'), '')
            ->baseUrl($this->baseUrl());
    }

    private function baseUrl(): string
    {
        return config('services.midtrans.is_production')
            ? 'https://api.midtrans.com'
            : 'https://api.sandbox.midtrans.com';
    }

    private function normalizeCreatePayload(string $reference, array $payload): array
    {
        $actions = collect($payload['actions'] ?? []);
        $generateQrAction = $actions->first(fn (array $action): bool => ($action['name'] ?? null) === 'generate-qr-code');

        return array_merge($this->normalizeStatusPayload($reference, $payload), [
            'qr_string' => $payload['qr_string'] ?? null,
            'qr_url' => $generateQrAction['url'] ?? null,
            'checkout_url' => null,
            'expires_at' => $this->normalizeDate($payload['expiry_time'] ?? null),
            'provider_payload' => $payload,
        ]);
    }

    private function normalizeStatusPayload(string $reference, array $payload): array
    {
        $rawStatus = Str::lower((string) ($payload['transaction_status'] ?? 'pending'));

        return [
            'gateway' => 'midtrans',
            'reference' => $reference,
            'transaction_id' => $payload['transaction_id'] ?? null,
            'gateway_status' => $rawStatus,
            'normalized_status' => match ($rawStatus) {
                'settlement', 'capture' => 'confirmed',
                'expire', 'cancel', 'deny', 'failure' => 'cancelled',
                default => 'pending',
            },
            'expires_at' => $this->normalizeDate($payload['expiry_time'] ?? null),
            'provider_payload' => $payload,
        ];
    }

    private function acquirer(PaymentMethod $paymentMethod): string
    {
        $provider = Str::lower((string) $paymentMethod->provider);

        return str_contains($provider, 'shopee') ? 'airpay shopee' : 'gopay';
    }

    private function normalizeDate(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        return now()->parse($value)->toIso8601String();
    }
}
