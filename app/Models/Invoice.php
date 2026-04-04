<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEntity;
use App\Models\Concerns\HasUlidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory;
    use HasUlidPrimaryKey;
    use BelongsToEntity;
    use SoftDeletes;

    protected $fillable = [
        'entity_id',
        'customer_id',
        'recurring_template_id',
        'invoice_number',
        'status',
        'invoice_date',
        'due_date',
        'subtotal',
        'discount_total',
        'tax_total',
        'grand_total',
        'amount_paid',
        'amount_due',
        'currency',
        'notes',
        'terms',
        'pdf_path',
        'public_token',
        'sent_at',
        'paid_at',
        'cancelled_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'amount_due' => 'decimal:2',
            'sent_at' => 'datetime',
            'paid_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(InvoiceStatusHistory::class);
    }

    public function recurringTemplate(): BelongsTo
    {
        return $this->belongsTo(RecurringTemplate::class);
    }
}
