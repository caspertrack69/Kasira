<?php

namespace App\Console\Commands;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Illuminate\Console\Command;

class MarkOverdueInvoicesCommand extends Command
{
    protected $signature = 'kasira:invoices:mark-overdue';

    protected $description = 'Mark sent or partial invoices as overdue when due date has passed';

    public function handle(): int
    {
        $affected = Invoice::query()
            ->withoutGlobalScopes()
            ->whereIn('status', [InvoiceStatus::Sent->value, InvoiceStatus::Partial->value])
            ->whereDate('due_date', '<', now()->toDateString())
            ->update(['status' => InvoiceStatus::Overdue->value]);

        $this->info("Marked {$affected} invoices as overdue.");

        return self::SUCCESS;
    }
}
