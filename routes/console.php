<?php

use App\Console\Commands\DispatchInvoiceRemindersCommand;
use App\Console\Commands\GenerateRecurringInvoicesCommand;
use App\Console\Commands\MarkOverdueInvoicesCommand;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(
        collect([
            'Simplicity is the ultimate sophistication.',
            'Success is the sum of small efforts repeated every day.',
            'Software is a great combination between artistry and engineering.',
        ])->random()
    );
})->purpose('Display an inspiring quote');

Schedule::command(MarkOverdueInvoicesCommand::class)->dailyAt('00:30');
Schedule::command(GenerateRecurringInvoicesCommand::class)->dailyAt('01:00');
Schedule::command(DispatchInvoiceRemindersCommand::class)->dailyAt('08:00');
