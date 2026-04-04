<?php

namespace App\Console\Commands;

use App\Enums\InvoiceStatus;
use App\Jobs\SendOverdueReminderJob;
use App\Models\Invoice;
use Illuminate\Console\Command;

class DispatchInvoiceRemindersCommand extends Command
{
    protected $signature = 'kasira:notifications:dispatch-reminders';

    protected $description = 'Dispatch overdue invoice reminders';

    public function handle(): int
    {
        $today = now()->startOfDay();

        $invoices = Invoice::query()
            ->withoutGlobalScopes()
            ->with('entity')
            ->where('status', InvoiceStatus::Overdue->value)
            ->whereNotNull('due_date')
            ->get()
            ->filter(function (Invoice $invoice) use ($today): bool {
                $reminderDays = collect($invoice->entity?->reminder_days ?? [1, 3, 7])
                    ->filter(fn ($day) => is_numeric($day))
                    ->map(fn ($day) => (int) $day)
                    ->unique()
                    ->values();

                if ($reminderDays->isEmpty()) {
                    return false;
                }

                $daysOverdue = $invoice->due_date?->startOfDay()->diffInDays($today, false);

                return $daysOverdue !== null
                    && $daysOverdue > 0
                    && $reminderDays->contains((int) floor($daysOverdue));
            });

        foreach ($invoices as $invoice) {
            SendOverdueReminderJob::dispatch($invoice->getKey());
        }

        $this->info('Queued '.$invoices->count().' reminder jobs.');

        return self::SUCCESS;
    }
}
