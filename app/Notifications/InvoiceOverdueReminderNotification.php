<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceOverdueReminderNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Invoice $invoice)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('invoices.public.show', ['token' => $this->invoice->public_token]);

        return (new MailMessage())
            ->subject('Overdue Invoice Reminder: '.$this->invoice->invoice_number)
            ->line('Your invoice is overdue. Please complete payment as soon as possible.')
            ->line('Outstanding: '.$this->invoice->amount_due)
            ->action('View Invoice', $url);
    }
}
