<?php

namespace App\Providers;

use App\Models\CreditNote;
use App\Models\Customer;
use App\Models\Entity;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\RecurringTemplate;
use App\Models\Tax;
use App\Models\User;
use App\Policies\CreditNotePolicy;
use App\Policies\CustomerPolicy;
use App\Policies\EntityPolicy;
use App\Policies\InvoicePolicy;
use App\Policies\ItemPolicy;
use App\Policies\PaymentMethodPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\RecurringTemplatePolicy;
use App\Policies\TaxPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Entity::class => EntityPolicy::class,
        Customer::class => CustomerPolicy::class,
        Tax::class => TaxPolicy::class,
        Item::class => ItemPolicy::class,
        PaymentMethod::class => PaymentMethodPolicy::class,
        Invoice::class => InvoicePolicy::class,
        Payment::class => PaymentPolicy::class,
        CreditNote::class => CreditNotePolicy::class,
        RecurringTemplate::class => RecurringTemplatePolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::before(static function (User $user): ?bool {
            return $user->isSuperAdmin() ? true : null;
        });
    }
}
