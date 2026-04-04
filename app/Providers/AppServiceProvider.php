<?php

namespace App\Providers;

use App\Models\CreditNote;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\RecurringTemplate;
use App\Models\Tax;
use App\Observers\AuditableObserver;
use App\Support\EntityContext;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(EntityContext::class, fn (): EntityContext => new EntityContext());
    }

    public function boot(): void
    {
        View::composer('*', function ($view): void {
            $view->with('activeEntity', app(EntityContext::class)->entity());
        });

        Customer::observe(AuditableObserver::class);
        Tax::observe(AuditableObserver::class);
        Item::observe(AuditableObserver::class);
        PaymentMethod::observe(AuditableObserver::class);
        Invoice::observe(AuditableObserver::class);
        Payment::observe(AuditableObserver::class);
        CreditNote::observe(AuditableObserver::class);
        RecurringTemplate::observe(AuditableObserver::class);
    }
}
