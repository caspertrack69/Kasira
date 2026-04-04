<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Models\NotificationLog;
use App\Notifications\InvoiceOverdueReminderNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Notification;

class SendOverdueReminderJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $invoiceId)
    {
    }

    public function handle(): void
    {
        $invoice = Invoice::query()->withoutGlobalScopes()->with(['customer'])->findOrFail($this->invoiceId);
        if (! $invoice->customer?->email) {
            return;
        }

        Notification::route('mail', $invoice->customer->email)->notify(new InvoiceOverdueReminderNotification($invoice));

        NotificationLog::query()->create([
            'entity_id' => $invoice->entity_id,
            'notifiable_type' => $invoice->customer::class,
            'notifiable_id' => $invoice->customer->getKey(),
            'channel' => 'email',
            'event_type' => 'invoice_overdue_reminder',
            'recipient' => $invoice->customer->email,
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }
}
