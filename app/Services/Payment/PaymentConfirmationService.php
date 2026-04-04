<?php

namespace App\Services\Payment;

use App\Enums\PaymentStatus;
use App\Models\NotificationLog;
use App\Models\Payment;
use App\Notifications\PaymentReceivedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class PaymentConfirmationService
{
    public function __construct(
        private readonly PaymentAllocationService $allocationService,
    ) {
    }

    public function confirm(Payment $payment, int $userId): void
    {
        if ($payment->status === PaymentStatus::Confirmed->value) {
            return;
        }

        DB::transaction(function () use ($payment, $userId): void {
            $payment->update([
                'status' => PaymentStatus::Confirmed->value,
                'confirmed_by' => $userId,
                'confirmed_at' => now(),
            ]);

            $this->allocationService->applyConfirmedAllocations($payment);
        });

        $payment->loadMissing('customer');

        if (! $payment->customer?->email) {
            return;
        }

        Notification::route('mail', $payment->customer->email)->notify(new PaymentReceivedNotification($payment));

        NotificationLog::query()->create([
            'entity_id' => $payment->entity_id,
            'notifiable_type' => $payment->customer::class,
            'notifiable_id' => $payment->customer->getKey(),
            'subject_type' => $payment::class,
            'subject_id' => $payment->getKey(),
            'channel' => 'email',
            'event_type' => 'payment_received',
            'recipient' => $payment->customer->email,
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }
}
