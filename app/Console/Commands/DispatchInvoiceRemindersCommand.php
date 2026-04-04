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
        $invoices = Invoice::query()
            ->withoutGlobalScopes()
            ->where('status', InvoiceStatus::Overdue->value)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<=', now()->subDays(1)->toDateString())
            ->get();

        foreach ($invoices as $invoice) {
            SendOverdueReminderJob::dispatch($invoice->getKey());
        }

        $this->info('Queued '.$invoices->count().' reminder jobs.');

        return self::SUCCESS;
    }
}
