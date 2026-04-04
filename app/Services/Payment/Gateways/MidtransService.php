<?php

namespace App\Services\Payment\Gateways;

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
}
