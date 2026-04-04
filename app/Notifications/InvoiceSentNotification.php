<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceSentNotification extends Notification
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
            ->subject('Invoice '.$this->invoice->invoice_number)
            ->greeting('Hello,')
            ->line('A new invoice has been issued to you.')
            ->line('Invoice Number: '.$this->invoice->invoice_number)
            ->line('Total: '.$this->invoice->grand_total)
            ->action('View Invoice', $url);
    }
}
