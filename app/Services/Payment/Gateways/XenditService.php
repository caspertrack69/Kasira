<?php

namespace App\Services\Payment\Gateways;

class XenditService
{
    public function validateCallbackToken(?string $token): bool
    {
        $configured = (string) config('services.xendit.callback_token');

        return $configured !== '' && $token !== null && hash_equals($configured, $token);
    }
}
