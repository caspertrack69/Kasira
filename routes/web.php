<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\CreditNoteController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EntityController;
use App\Http\Controllers\EntitySwitchController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoicePdfController;
use App\Http\Controllers\InvoiceSendController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\NotificationLogController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\PaymentProofController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicInvoiceController;
use App\Http\Controllers\PublicInvoicePdfController;
use App\Http\Controllers\RecurringTemplateController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportDownloadController;
use App\Http\Controllers\SystemSettingController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Webhook\MidtransWebhookController;
use App\Http\Controllers\Webhook\XenditWebhookController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::get('/invoice/{token}', [PublicInvoiceController::class, 'show'])->name('invoices.public.show');
Route::get('/invoice/{token}/download', PublicInvoicePdfController::class)->name('invoices.public.download');
Route::post('/webhooks/midtrans', MidtransWebhookController::class)->name('webhooks.midtrans');
Route::post('/webhooks/xendit', XenditWebhookController::class)->name('webhooks.xendit');

Route::middleware(['auth'])->group(function (): void {
    Route::get('/dashboard', DashboardController::class)
        ->middleware(['verified', 'entity.context'])
        ->name('dashboard');

    Route::post('/entity/switch/{entity}', EntitySwitchController::class)
        ->middleware(['entity.context'])
        ->name('entities.switch');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware(['entity.context'])->group(function (): void {
        Route::resource('entities', EntityController::class)->except(['create', 'edit', 'show']);
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::post('users/{user}/assign', [UserController::class, 'assign'])->name('users.assign');

        Route::resource('customers', CustomerController::class)->except(['create', 'edit', 'show']);
        Route::resource('taxes', TaxController::class)->except(['create', 'edit', 'show']);
        Route::resource('items', ItemController::class)->except(['create', 'edit', 'show']);
        Route::resource('payment-methods', PaymentMethodController::class)->except(['create', 'edit', 'show']);

        Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::post('invoices', [InvoiceController::class, 'store'])->name('invoices.store');
        Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
        Route::get('invoices/{invoice}/pdf', InvoicePdfController::class)->name('invoices.pdf');
        Route::post('invoices/{invoice}/send', InvoiceSendController::class)->name('invoices.send');
        Route::post('invoices/{invoice}/duplicate', [InvoiceController::class, 'duplicate'])->name('invoices.duplicate');
        Route::post('invoices/{invoice}/void', [InvoiceController::class, 'void'])->name('invoices.void');

        Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::post('payments', [PaymentController::class, 'store'])->name('payments.store');
        Route::get('payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
        Route::get('payments/{payment}/proof', PaymentProofController::class)->name('payments.proof');
        Route::post('payments/{payment}/confirm', [PaymentController::class, 'confirm'])->name('payments.confirm');

        Route::get('credit-notes', [CreditNoteController::class, 'index'])->name('credit-notes.index');
        Route::post('credit-notes', [CreditNoteController::class, 'store'])->name('credit-notes.store');

        Route::get('recurring-templates', [RecurringTemplateController::class, 'index'])->name('recurring-templates.index');
        Route::post('recurring-templates', [RecurringTemplateController::class, 'store'])->name('recurring-templates.store');
        Route::put('recurring-templates/{recurringTemplate}', [RecurringTemplateController::class, 'update'])->name('recurring-templates.update');
        Route::delete('recurring-templates/{recurringTemplate}', [RecurringTemplateController::class, 'destroy'])->name('recurring-templates.destroy');

        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/export/csv', [ReportController::class, 'exportCsv'])->name('reports.export.csv');
        Route::post('reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');
        Route::get('reports/export/pdf/download', ReportDownloadController::class)->name('reports.export.pdf.download');

        Route::get('notification-logs', [NotificationLogController::class, 'index'])->name('notification-logs.index');
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

        Route::get('settings', [SystemSettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [SystemSettingController::class, 'update'])->name('settings.update');
    });
});

require __DIR__.'/auth.php';
