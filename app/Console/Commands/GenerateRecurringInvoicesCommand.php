<?php

namespace App\Console\Commands;

use App\Services\Billing\RecurringInvoiceService;
use Illuminate\Console\Command;

class GenerateRecurringInvoicesCommand extends Command
{
    protected $signature = 'kasira:invoices:generate-recurring';

    protected $description = 'Generate invoices from active recurring templates';

    public function handle(RecurringInvoiceService $service): int
    {
        $count = $service->generateDueTemplates();

        $this->info("Generated {$count} recurring invoices.");

        return self::SUCCESS;
    }
}
