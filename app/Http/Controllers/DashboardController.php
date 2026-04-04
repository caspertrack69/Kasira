<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Models\Invoice;
use App\Models\Payment;
use App\Support\EntityContext;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(EntityContext $context): View
    {
        $entityId = $context->id();

        $invoiceQuery = Invoice::query()
            ->withoutGlobalScopes()
            ->whereNotIn('status', [
                InvoiceStatus::Draft->value,
                InvoiceStatus::Cancelled->value,
                InvoiceStatus::Void->value,
            ]);

        $paymentQuery = Payment::query()
            ->withoutGlobalScopes()
            ->where('status', PaymentStatus::Confirmed->value);

        if ($entityId) {
            $invoiceQuery->where('entity_id', $entityId);
            $paymentQuery->where('entity_id', $entityId);
        }

        $outstandingQuery = $invoiceQuery->clone()->where('amount_due', '>', 0);
        $overdueQuery = $outstandingQuery->clone()->where('status', InvoiceStatus::Overdue->value);

        return view('dashboard', [
            'outstandingCount' => $outstandingQuery->clone()->count(),
            'outstandingTotal' => $outstandingQuery->clone()->sum('amount_due'),
            'overdueCount' => $overdueQuery->clone()->count(),
            'overdueTotal' => $overdueQuery->clone()->sum('amount_due'),
            'paymentCount' => $paymentQuery->clone()->count(),
            'paymentTotal' => $paymentQuery->clone()->sum('amount'),
            'recentPayments' => $paymentQuery->clone()->latest('payment_date')->take(10)->get(),
        ]);
    }
}
