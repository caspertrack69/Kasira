<?php

namespace App\Services\Payment;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Services\Payment\Gateways\MidtransService;
use App\Services\Payment\Gateways\XenditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OnlinePaymentService
{
    public function __construct(
        private readonly PaymentAllocationService $allocationService,
        private readonly PaymentConfirmationService $confirmationService,
        private readonly MidtransService $midtransService,
        private readonly XenditService $xenditService,
    ) {
    }

    public function currentGatewayPayment(Invoice $invoice): ?Payment
    {
        if ((float) $invoice->amount_due <= 0) {
            return null;
        }

        $candidates = Payment::query()
            ->withoutGlobalScopes()
            ->with('paymentMethod')
            ->where('entity_id', $invoice->entity_id)
            ->where('status', PaymentStatus::Pending->value)
            ->whereHas('allocations', fn ($query) => $query->where('invoice_id', $invoice->getKey()))
            ->whereHas('paymentMethod', fn ($query) => $query->where('type', 'qris'))
            ->latest('created_at')
            ->get();

        foreach ($candidates as $payment) {
            if ($this->isReusablePendingPayment($payment)) {
                return $payment;
            }
        }

        return null;
    }

    public function startQrisPayment(Invoice $invoice): Payment
    {
        abort_if(! in_array($invoice->status, [InvoiceStatus::Sent->value, InvoiceStatus::Partial->value, InvoiceStatus::Overdue->value], true), 422, 'Invoice is not payable online.');
        abort_if((float) $invoice->amount_due <= 0, 422, 'Invoice no longer has an outstanding balance.');

        if ($existing = $this->currentGatewayPayment($invoice)) {
            return $existing;
        }

        $paymentMethod = $this->resolveQrisPaymentMethod($invoice);
        $paymentNumber = $this->paymentNumber($invoice);
        $reference = $this->paymentReference($invoice);
        $gatewayPayload = $this->createGatewayPayment($paymentMethod, $invoice, $paymentNumber, $reference);

        return DB::transaction(function () use ($invoice, $paymentMethod, $paymentNumber, $reference, $gatewayPayload): Payment {
            $payment = Payment::query()->create([
                'entity_id' => $invoice->entity_id,
                'customer_id' => $invoice->customer_id,
                'payment_method_id' => $paymentMethod->getKey(),
                'payment_number' => $paymentNumber,
                'amount' => $invoice->amount_due,
                'payment_date' => now()->toDateString(),
                'reference' => $reference,
                'status' => PaymentStatus::Pending->value,
                'notes' => $paymentMethod->instructions,
                'gateway_response' => $gatewayPayload,
                'created_by' => $invoice->created_by,
            ]);

            $this->allocationService->allocate($payment, [
                $invoice->getKey() => $invoice->amount_due,
            ]);

            return $payment->fresh(['paymentMethod', 'allocations']);
        });
    }

    public function syncInvoicePaymentStatus(Invoice $invoice): ?Payment
    {
        $payment = $this->currentGatewayPayment($invoice);

        return $payment ? $this->syncPaymentStatus($payment) : null;
    }

    public function syncPaymentStatus(Payment $payment): Payment
    {
        if ($payment->status !== PaymentStatus::Pending->value) {
            return $payment->loadMissing('paymentMethod');
        }

        $payment->loadMissing('paymentMethod');
        $gateway = $this->gatewayName($payment->paymentMethod, $payment->gateway_response ?? []);

        $statusPayload = match ($gateway) {
            'midtrans' => $this->midtransService->fetchPaymentStatus($payment),
            'xendit' => $this->xenditService->fetchPaymentStatus($payment),
            default => [],
        };

        if ($statusPayload === []) {
            return $payment;
        }

        return $this->applyGatewayStatusUpdate($payment, $statusPayload);
    }

    public function applyGatewayStatusUpdate(Payment $payment, array $statusPayload): Payment
    {
        $mergedPayload = array_replace($payment->gateway_response ?? [], $statusPayload);

        if (($statusPayload['normalized_status'] ?? 'pending') === 'confirmed') {
            $payment->forceFill(['gateway_response' => $mergedPayload])->save();
            $this->confirmationService->confirm($payment, null);
            $payment->refresh();
            $payment->forceFill(['gateway_response' => $mergedPayload])->save();

            return $payment->fresh(['paymentMethod', 'allocations.invoice']);
        }

        if (($statusPayload['normalized_status'] ?? 'pending') === 'cancelled' && $payment->status === PaymentStatus::Pending->value) {
            $payment->update([
                'status' => PaymentStatus::Cancelled->value,
                'gateway_response' => $mergedPayload,
            ]);

            return $payment->fresh(['paymentMethod', 'allocations.invoice']);
        }

        $payment->update(['gateway_response' => $mergedPayload]);

        return $payment->fresh(['paymentMethod', 'allocations.invoice']);
    }

    public function paymentData(?Payment $payment): ?array
    {
        if (! $payment) {
            return null;
        }

        $payload = $payment->gateway_response ?? [];

        return [
            'gateway' => $payload['gateway'] ?? $this->gatewayName($payment->paymentMethod, $payload),
            'status' => $payment->status,
            'payment_number' => $payment->payment_number,
            'reference' => $payment->reference,
            'qr_string' => $payload['qr_string'] ?? null,
            'qr_url' => $payload['qr_url'] ?? null,
            'checkout_url' => $payload['checkout_url'] ?? null,
            'expires_at' => $payload['expires_at'] ?? null,
            'gateway_status' => $payload['gateway_status'] ?? null,
        ];
    }

    private function resolveQrisPaymentMethod(Invoice $invoice): PaymentMethod
    {
        return PaymentMethod::query()
            ->where('entity_id', $invoice->entity_id)
            ->where('type', 'qris')
            ->where('is_active', true)
            ->latest('updated_at')
            ->firstOrFail();
    }

    private function createGatewayPayment(PaymentMethod $paymentMethod, Invoice $invoice, string $paymentNumber, string $reference): array
    {
        $gateway = $this->gatewayName($paymentMethod);

        return match ($gateway) {
            'midtrans' => $this->midtransService->createQrisPayment($invoice, $paymentMethod, $paymentNumber, $reference),
            'xendit' => $this->xenditService->createQrisPayment($invoice, $paymentMethod, $paymentNumber, $reference),
            default => throw new \RuntimeException('Unsupported QRIS payment gateway provider.'),
        };
    }

    private function isReusablePendingPayment(Payment $payment): bool
    {
        $payload = $payment->gateway_response ?? [];
        $expiresAt = $payload['expires_at'] ?? null;

        if ($expiresAt && now()->greaterThan(now()->parse($expiresAt))) {
            $payment->update(['status' => PaymentStatus::Cancelled->value]);

            return false;
        }

        return ! empty($payload['qr_string']) || ! empty($payload['checkout_url']);
    }

    private function paymentNumber(Invoice $invoice): string
    {
        return sprintf('PAY-%s-%s', now()->format('YmdHis'), strtoupper(Str::random(6)));
    }

    private function paymentReference(Invoice $invoice): string
    {
        return sprintf('%s-QR-%s', $invoice->invoice_number, strtoupper(Str::random(10)));
    }

    private function gatewayName(?PaymentMethod $paymentMethod, array $payload = []): string
    {
        $gateway = Str::lower((string) ($payload['gateway'] ?? $paymentMethod?->provider ?? 'midtrans'));

        return str_contains($gateway, 'xendit') ? 'xendit' : 'midtrans';
    }
}
